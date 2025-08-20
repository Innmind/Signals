<?php
declare(strict_types = 1);

namespace Innmind\Signals\Handler;

use Innmind\Signals\{
    Signal,
    Info,
};
use Innmind\Immutable\{
    Sequence,
    Map,
    Maybe,
};

/**
 * @internal
 */
final class Main
{
    /** @var Map<Signal, Sequence<callable(Signal, Info): void>> */
    private Map $handlers;
    private bool $wasAsync;

    private function __construct()
    {
        /** @var Map<Signal, Sequence<callable(Signal, Info): void>> */
        $this->handlers = Map::of();
        $this->wasAsync = \pcntl_async_signals();
        \pcntl_async_signals(true);
    }

    public static function install(): self
    {
        return new self;
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void
    {
        $handlers = $this->installSignal($signal);
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
            static fn($_, $listeners) => $listeners->exclude(
                static fn($callable) => $callable === $listener,
            ),
        );
        $_ = $handlers->foreach(static function($signal, $listeners): void {
            if ($listeners->empty()) {
                \pcntl_signal($signal->toInt(), \SIG_DFL); // restore default handler
            }
        });
        $this->handlers = $handlers->exclude(
            static fn($_, $listeners) => $listeners->empty(),
        );

        if ($this->handlers->empty()) {
            \pcntl_async_signals($this->wasAsync);
        }
    }

    /**
     * @return Sequence<callable(Signal, Info): void>
     */
    private function installSignal(Signal $signal): Sequence
    {
        return $this
            ->handlers
            ->get($signal)
            ->otherwise(function() use ($signal) {
                \pcntl_signal($signal->toInt(), function($signo, $siginfo): void {
                    $this->dispatch(Signal::of($signo), $siginfo);
                });

                /** @var Maybe<Sequence<callable(Signal, Info): void>> */
                return Maybe::nothing();
            })
            ->toSequence()
            ->flatMap(static fn($listeners) => $listeners);
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

        $_ = $this
            ->handlers
            ->get($signal)
            ->toSequence()
            ->flatMap(static fn($listeners) => $listeners)
            ->foreach(static fn($listener) => $listener($signal, $structure));
    }
}
