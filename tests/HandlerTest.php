<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals;

use Innmind\Signals\{
    Handler,
    Signal,
    Async\Interceptor,
};
use Innmind\Immutable\SideEffect;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class HandlerTest extends TestCase
{
    use BlackBox;

    public function testNothingHappensByDefaultWhenReceivingSignal()
    {
        $async = \pcntl_async_signals();
        $handler = Handler::main();

        $this->fork();

        $this->assertSame($async, \pcntl_async_signals());
    }

    public function testAllListenersAreCalledInOrderOnSignal()
    {
        $handlers = Handler::main();
        $order = [];
        $count = 0;

        $this->fork();

        $this->assertInstanceOf(
            SideEffect::class,
            $handlers
                ->listen(Signal::child, function($signal) use (&$order, &$count): void {
                    static $handled = false;

                    if ($handled) {
                        return;
                    }

                    $handled = true;
                    $this->assertSame(Signal::child, $signal);
                    $order[] = 'first';
                    ++$count;
                })
                ->unwrap(),
        );
        $_ = $handlers->listen(Signal::child, function($signal) use (&$order, &$count): void {
            static $handled = false;

            if ($handled) {
                return;
            }

            $handled = true;
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        })->unwrap();

        \sleep(2); // wait for child to stop

        $this->assertSame(2, $count);
        $this->assertSame(['first', 'second'], $order);
    }

    public function testRemoveSignal()
    {
        $handlers = Handler::main();
        $order = [];
        $count = 0;

        $this->fork();

        $first = function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'first';
            ++$count;
        };
        $_ = $handlers->listen(Signal::child, $first)->unwrap();
        $_ = $handlers->listen(Signal::child, function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        })->unwrap();
        $this->assertInstanceOf(
            SideEffect::class,
            $handlers->remove($first)->unwrap(),
        );

        \sleep(2); // wait for child to stop

        $this->assertSame(1, $count, \implode(', ', $order));
        $this->assertSame(['second'], $order);
    }

    public function testDefaultHandlerRestoredWhenAllListenersRemovedForASignal()
    {
        $wasAsync = \pcntl_async_signals();
        $handlers = Handler::main();
        $order = [];
        $count = 0;

        $this->fork();

        $listener = static function($signal) use (&$order, &$count): void {
            $order[] = 'first';
            ++$count;
        };
        $_ = $handlers->listen(Signal::child, $listener)->unwrap();
        $_ = $handlers->remove($listener)->unwrap();

        $this->assertSame($wasAsync, \pcntl_async_signals());

        \sleep(2); // wait for child to stop

        $this->assertSame(0, $count);
        $this->assertSame([], $order);
    }

    public function testAsyncHandlers(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::of(...Signal::cases()))
            ->prove(function($signal) {
                $main = Handler::main();
                $interceptor = Interceptor::new();
                $async = Handler::async($main, $interceptor);

                $called = false;
                $_ = $async->listen($signal, function($in) use ($signal, &$called) {
                    $this->assertSame($signal, $in);
                    $called = true;
                })->unwrap();
                $interceptor->dispatch($signal);

                $this->assertTrue($called);
            });
    }

    public function testRemovedAsyncListenersAreNotCalled(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::of(...Signal::cases()))
            ->prove(function($signal) {
                $main = Handler::main();
                $interceptor = Interceptor::new();
                $async = Handler::async($main, $interceptor);

                $called = 0;
                $listener = function($in) use ($signal, &$called) {
                    $this->assertSame($signal, $in);
                    ++$called;
                };
                $_ = $async->listen($signal, $listener)->unwrap();
                $interceptor->dispatch($signal);

                $this->assertSame(1, $called);

                $_ = $async->remove($listener)->unwrap();
                $interceptor->dispatch($signal);

                $this->assertSame(1, $called);
            });
    }

    private function fork(): void
    {
        if (\pcntl_fork() === 0) {
            \sleep(1);

            exit;
        }
    }
}
