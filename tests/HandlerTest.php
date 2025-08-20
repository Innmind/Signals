<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals;

use Innmind\Signals\{
    Handler,
    Signal,
    Async\Interceptor,
};
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
        $handler = Handler::main();

        $this->fork();

        $this->assertTrue(\pcntl_async_signals());
    }

    public function testAllListenersAreCalledInOrderOnSignal()
    {
        $handlers = Handler::main();
        $order = [];
        $count = 0;

        $this->fork();

        $this->assertNull($handlers->listen(Signal::child, function($signal) use (&$order, &$count): void {
            static $handled = false;

            if ($handled) {
                return;
            }

            $handled = true;
            $this->assertSame(Signal::child, $signal);
            $order[] = 'first';
            ++$count;
        }));
        $handlers->listen(Signal::child, function($signal) use (&$order, &$count): void {
            static $handled = false;

            if ($handled) {
                return;
            }

            $handled = true;
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        });

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
        $handlers->listen(Signal::child, $first);
        $handlers->listen(Signal::child, function($signal) use (&$order, &$count): void {
            $this->assertSame(Signal::child, $signal);
            $order[] = 'second';
            ++$count;
        });
        $this->assertNull($handlers->remove($first));

        \sleep(2); // wait for child to stop

        $this->assertSame(1, $count);
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
        $handlers->listen(Signal::child, $listener);
        $handlers->remove($listener);

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
                $async = $main->async($interceptor);

                $called = false;
                $async->listen($signal, function($in) use ($signal, &$called) {
                    $this->assertSame($signal, $in);
                    $called = true;
                });
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
                $async = $main->async($interceptor);

                $called = 0;
                $listener = function($in) use ($signal, &$called) {
                    $this->assertSame($signal, $in);
                    ++$called;
                };
                $async->listen($signal, $listener);
                $interceptor->dispatch($signal);

                $this->assertSame(1, $called);

                $async->remove($listener);
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
