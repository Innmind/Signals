<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Signals\{
    Handler\Main,
    Handler\Async,
    Async\Interceptor,
};

final class Handler
{
    /**
     * @psalm-mutation-free
     */
    private function __construct(
        private Main|Async $implementation,
    ) {
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function main(): self
    {
        return new self(Main::install());
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function listen(Signal $signal, callable $listener): void
    {
        $this->implementation->listen($signal, $listener);
    }

    /**
     * @param callable(Signal, Info): void $listener
     */
    public function remove(callable $listener): void
    {
        $this->implementation->remove($listener);
    }

    /**
     * This is intended to build a child handler inside a Fiber.
     * The interceptor allows to emulate a signals to send a fake signal to
     * instruct the fiber to terminate.
     *
     * @internal
     * @psalm-mutation-free
     */
    #[\NoDiscard]
    public function async(?Interceptor $interceptor = null): self
    {
        return new self(
            Async::new($this, $interceptor),
        );
    }
}
