<?php
declare(strict_types = 1);

namespace Innmind\Signals\Handler;

use Innmind\Signals\{
    Handler,
    Signal,
    Info,
    Async\Interceptor,
};
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

/**
 * @internal
 */
final class Async
{
    /**
     * @psalm-mutation-free
     */
    private function __construct(
        private Handler $parent,
        private ?Interceptor $interceptor,
    ) {
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function new(Handler $parent, ?Interceptor $interceptor): self
    {
        return new self($parent, $interceptor);
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function listen(Signal $signal, callable $listener): Attempt
    {
        return $this
            ->parent
            ->listen($signal, $listener)
            ->map(function($_) use ($signal, $listener) {
                $this->interceptor?->listen($signal, $listener);

                return $_;
            });
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function remove(callable $listener): Attempt
    {
        return $this
            ->parent
            ->remove($listener)
            ->map(function($_) use ($listener) {
                $this->interceptor?->remove($listener);

                return $_;
            });
    }
}
