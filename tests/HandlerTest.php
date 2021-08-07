<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals;

use Innmind\Signals\{
    Handler,
    Signal,
};
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testNothingHappensByDefaultWhenReceivingSignal()
    {
        $handler = new Handler;

        $this->fork();

        $this->assertTrue(\pcntl_async_signals());
    }

    public function testAllListenersAreCalledInOrderOnSignal()
    {
        $handlers = new Handler;
        $order = [];
        $count = 0;

        $this->fork();

        $this->assertNull($handlers->listen(Signal::child(), function($signal) use (&$order, &$count): void {
            $this->assertEquals(Signal::child(), $signal);
            $order[] = 'first';
            ++$count;
        }));
        $handlers->listen(Signal::child(), function($signal) use (&$order, &$count): void {
            $this->assertEquals(Signal::child(), $signal);
            $order[] = 'second';
            ++$count;
        });

        \sleep(2); // wait for child to stop

        $this->assertSame(2, $count);
        $this->assertSame(['first', 'second'], $order);
    }

    public function testRemoveSignal()
    {
        $handlers = new Handler;
        $order = [];
        $count = 0;

        $this->fork();

        $first = function($signal) use (&$order, &$count): void {
            $this->assertEquals(Signal::child(), $signal);
            $order[] = 'first';
            ++$count;
        };
        $handlers->listen(Signal::child(), $first);
        $handlers->listen(Signal::child(), function($signal) use (&$order, &$count): void {
            $this->assertEquals(Signal::child(), $signal);
            $order[] = 'second';
            ++$count;
        });
        $this->assertNull($handlers->remove($first));

        \sleep(2); // wait for child to stop

        $this->assertSame(1, $count);
        $this->assertSame(['second'], $order);
    }

    public function testListenersAddedAfterAResetAreNotCalled()
    {
        $wasAsync = \pcntl_async_signals();
        $handlers = new Handler;
        $order = [];
        $count = 0;

        $this->fork();

        $listener = static function($signal) use (&$order, &$count): void {
            $order[] = 'first';
            ++$count;
        };
        $handlers->listen(Signal::child(), $listener);

        $this->assertNull($handlers->reset());
        $this->assertSame($wasAsync, \pcntl_async_signals());

        try {
            $handlers->listen(Signal::child(), $listener);
            $this->fail('it should throw');
        } catch (\Exception $e) {
            $this->assertInstanceOf(\LogicException::class, $e);
        }

        \sleep(2); // wait for child to stop

        $this->assertSame(0, $count);
        $this->assertSame([], $order);
    }

    public function testDefaultHandlerRestoredWhenAllListenersRemovedForASignal()
    {
        $wasAsync = \pcntl_async_signals();
        $handlers = new Handler;
        $order = [];
        $count = 0;

        $this->fork();

        $listener = static function($signal) use (&$order, &$count): void {
            $order[] = 'first';
            ++$count;
        };
        $handlers->listen(Signal::child(), $listener);
        $handlers->remove($listener);

        $this->assertSame($wasAsync, \pcntl_async_signals());

        \sleep(2); // wait for child to stop

        $this->assertSame(0, $count);
        $this->assertSame([], $order);
    }

    private function fork(): void
    {
        if (\pcntl_fork() === 0) {
            \sleep(1);

            exit;
        }
    }
}
