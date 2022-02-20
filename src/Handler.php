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
    /** @var Map<int, Sequence<callable(Signal, Info): void>> */
    private Map $handlers;
    /** @var Map<int, Signal> */
    private Map $ints;
    private bool $wasAsync;
    private bool $resetted = false;

    public function __construct()
    {
        /** @var Map<int, Sequence<callable(Signal, Info): void>> */
        $this->handlers = Map::of();
        /** @var Map<int, Signal> */
        $this->ints = Map::of();
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
            $signal->toInt(),
            ($handlers)($listener)
        );
        $this->ints = ($this->ints)($signal->toInt(), $signal);
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $handlers = $this->handlers->map(
            static fn(int $_, Sequence $listeners): Sequence => $listeners->filter(
                static fn(callable $callable): bool => $callable !== $listener,
            ),
        );
        $_ = $handlers->foreach(static function(int $signal, Sequence $listeners): void {
            if ($listeners->empty()) {
                \pcntl_signal($signal, \SIG_DFL); // restore default handler
            }
        });
        $this->handlers = $handlers->filter(
            static fn(int $_, Sequence $listeners): bool => !$listeners->empty(),
        );
    }

    public function reset(): void
    {
        $_ = $this->handlers->foreach(static function(int $signal): void {
            \pcntl_signal($signal, \SIG_DFL);
        });
        \pcntl_async_signals($this->wasAsync);
        $this->handlers = $this->handlers->clear();
        $this->ints = $this->ints->clear();
        $this->resetted = true;
    }

    /**
     * @return Sequence<callable(Signal, Info): void>
     */
    private function install(Signal $signal): Sequence
    {
        return $this
            ->handlers
            ->get($signal->toInt())
            ->match(
                static fn($listeners) => $listeners,
                function() use ($signal) {
                    /** @psalm-suppress MissingClosureParamType */
                    \pcntl_signal($signal->toInt(), function(int $signo, $siginfo): void {
                        /** @psalm-suppress MixedArgument */
                        $this->dispatch($signo, $siginfo);
                    });

                    /** @var Sequence<callable(Signal, Info): void> */
                    return Sequence::of();
                },
            );
    }

    /**
     * @param mixed $info
     */
    private function dispatch(int $signal, $info): void
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

        /**
         * @psalm-suppress MissingClosureReturnType
         * @var callable(callable(Signal, Info): void): void
         */
        $call = $this
            ->ints
            ->get($signal)
            ->match(
                static fn($signal) => static fn(callable $listen) => $listen($signal, $structure),
                static fn() => static fn() => null,
            );
        $_ = $this
            ->handlers
            ->get($signal)
            ->match(
                static fn($listeners) => $listeners,
                static fn() => Sequence::of(),
            )
            ->foreach(static fn(callable $listener) => $call($listener));
    }
}
