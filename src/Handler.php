<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\{
    Sequence,
    Map,
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
            return;
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
            static fn(int $_, Sequence $listeners): bool => !$listeners->empty()
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
        $structure = new Info;

        if (\is_array($info)) {
            /** @psalm-suppress MixedArgument */
            $structure = new Info(
                isset($info['code']) ? new Signal\Code($info['code']) : null,
                isset($info['errno']) ? new Signal\ErrorNumber($info['errno']) : null,
                isset($info['pid']) ? new Signal\SendingProcessId($info['pid']) : null,
                isset($info['uid']) ? new Signal\SendingProcessUserId($info['uid']) : null,
                isset($info['status']) ? new Signal\Status($info['status']) : null,
            );
        }

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
