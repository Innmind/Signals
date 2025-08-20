<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\{
    Sequence,
    Map,
    Maybe,
};

final class Handler
{
    /** @var Map<Signal, Sequence<callable(Signal, Info): void>> */
    private Map $handlers;
    private bool $wasAsync;
    private bool $resetted = false;

    private function __construct()
    {
        /** @var Map<Signal, Sequence<callable(Signal, Info): void>> */
        $this->handlers = Map::of();
        $this->wasAsync = \pcntl_async_signals();
        \pcntl_async_signals(true);
    }

    public static function main(): self
    {
        return new self;
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void
    {
        if ($this->resetted) {
            throw new \LogicException('Resetted handler is no longer usable');
        }

        $handlers = $this->install($signal);
        $this->handlers = ($this->handlers)(
            $signal,
            ($handlers)($listener)
        );
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $handlers = $this->handlers->map(
            static fn($_, Sequence $listeners): Sequence => $listeners->filter(
                static fn(callable $callable): bool => $callable !== $listener,
            ),
        );
        $_ = $handlers->foreach(static function(Signal $signal, Sequence $listeners): void {
            if ($listeners->empty()) {
                \pcntl_signal($signal->toInt(), \SIG_DFL); // restore default handler
            }
        });
        $this->handlers = $handlers->filter(
            static fn($_, Sequence $listeners): bool => !$listeners->empty(),
        );
    }

    public function reset(): void
    {
        $_ = $this->handlers->foreach(static function(Signal $signal): void {
            \pcntl_signal($signal->toInt(), \SIG_DFL);
        });
        \pcntl_async_signals($this->wasAsync);
        $this->handlers = $this->handlers->clear();
        $this->resetted = true;
    }

    /**
     * @return Sequence<callable(Signal, Info): void>
     */
    private function install(Signal $signal): Sequence
    {
        return $this
            ->handlers
            ->get($signal)
            ->match(
                static fn($listeners) => $listeners,
                function() use ($signal) {
                    \pcntl_signal($signal->toInt(), function(int $signo, mixed $siginfo): void {
                        $this->dispatch(Signal::of($signo), $siginfo);
                    });

                    /** @var Sequence<callable(Signal, Info): void> */
                    return Sequence::of();
                },
            );
    }

    private function dispatch(Signal $signal, mixed $info): void
    {
        $info = \is_array($info) ? $info : [];
        $structure = Info::of(
            Maybe::of($info['code'] ?? null)->map(Signal\Code::of(...)),
            Maybe::of($info['errno'] ?? null)->map(Signal\ErrorNumber::of(...)),
            Maybe::of($info['pid'] ?? null)->map(Signal\SendingProcessId::of(...)),
            Maybe::of($info['uid'] ?? null)->map(Signal\SendingProcessUserId::of(...)),
            Maybe::of($info['status'] ?? null)->map(Signal\Status::of(...)),
        );

        /** @var Sequence<callable(Signal, Info): void> */
        $listeners = $this
            ->handlers
            ->get($signal)
            ->match(
                static fn($listeners) => $listeners,
                static fn() => Sequence::of(),
            );
        $_ = $listeners->foreach(static fn(callable $listener) => $listener($signal, $structure));
    }
}
