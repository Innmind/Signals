<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Signals\Handler\{
    Main,
};

final class Handler
{
    private function __construct(
        private Main $implementation,
    ) {
    }

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
}
