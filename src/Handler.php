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

    public function __construct()
    {
        /** @var Map<Signal, Sequence<callable(Signal, Info): void>> */
        $this->handlers = Map::of();
        $this->wasAsync = \pcntl_async_signals();
        \pcntl_async_signals(true);
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
                    /** @psalm-suppress MissingClosureParamType */
                    \pcntl_signal($signal->toInt(), function(int $signo, $siginfo): void {
                        /** @psalm-suppress MixedArgument */
                        $this->dispatch(Signal::of($signo), $siginfo);
                    });

                    /** @var Sequence<callable(Signal, Info): void> */
                    return Sequence::of();
                },
            );
    }

    /**
     * @param mixed $info
     */
    private function dispatch(Signal $signal, $info): void
    {
        $info = \is_array($info) ? $info : [];
        /** @psalm-suppress MixedArgument */
        $structure = new Info(
            Maybe::of($info['code'] ?? null)->map(static fn($code) => new Signal\Code($code)),
            Maybe::of($info['errno'] ?? null)->map(static fn($errno) => new Signal\ErrorNumber($errno)),
            Maybe::of($info['pid'] ?? null)->map(static fn($pid) => new Signal\SendingProcessId($pid)),
            Maybe::of($info['uid'] ?? null)->map(static fn($uid) => new Signal\SendingProcessUserId($uid)),
            Maybe::of($info['status'] ?? null)->map(static fn($status) => new Signal\Status($status)),
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
