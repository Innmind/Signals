<?php
declare(strict_types = 1);

namespace Innmind\Signals\Async;

use Innmind\Signals\{
    Signal,
    Info,
};
use Innmind\Immutable\{
    Map,
    Sequence,
    Maybe,
};

/**
 * @internal
 */
final class Interceptor
{
    /**
     * @param Map<Signal, Sequence<callable(Signal, Info): void>> $handlers
     */
    private function __construct(
        private Map $handlers,
    ) {
    }

    public static function new(): self
    {
        return new self(Map::of());
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void
    {
        $this->handlers = ($this->handlers)(
            $signal,
            $this
                ->handlers
                ->get($signal)
                ->toSequence()
                ->flatMap(static fn($listeners) => $listeners)
                ->add($listener),
        );
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $this->handlers = $this->handlers->map(
            static fn($_, $listeners) => $listeners->exclude(
                static fn($known) => $known === $listener,
            ),
        );
    }

    public function dispatch(Signal $signal): void
    {
        /** @psalm-suppress MixedArgumentTypeCoercion */
        $info = Info::of(
            Maybe::nothing(),
            Maybe::nothing(),
            Maybe::nothing(),
            Maybe::nothing(),
            Maybe::nothing(),
        );
        $_ = $this
            ->handlers
            ->get($signal)
            ->toSequence()
            ->flatMap(static fn($listeners) => $listeners)
            ->foreach(static fn($listen) => $listen($signal, $info));
    }
}
