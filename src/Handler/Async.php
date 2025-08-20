<?php
declare(strict_types = 1);

namespace Innmind\Signals\Handler;

use Innmind\Signals\{
    Handler,
    Signal,
    Info,
    Async\Interceptor,
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
     */
    public function listen(Signal $signal, callable $listener): void
    {
        $this->parent->listen($signal, $listener);
        $this->interceptor?->listen($signal, $listener);
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $this->parent->remove($listener);
        $this->interceptor?->remove($listener);
    }
}
