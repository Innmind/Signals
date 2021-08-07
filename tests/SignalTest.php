<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals;

use Innmind\Signals\Signal;
use PHPUnit\Framework\TestCase;

class SignalTest extends TestCase
{
    /**
     * @dataProvider signals
     */
    public function testInterface($value, $name)
    {
        $signal = Signal::$name();

        $this->assertInstanceOf(Signal::class, $signal);
        $this->assertSame($value, $signal->toInt());
        $this->assertEquals($signal, Signal::$name());
        $this->assertTrue($signal->equals(Signal::$name()));
    }

    public function testEquals()
    {
        $this->assertTrue(Signal::illegal()->equals(Signal::illegal()));
        $this->assertFalse(Signal::illegal()->equals(Signal::terminate()));
    }

    public function signals(): array
    {
        return [
            [\SIGHUP, 'hangup'],
            [\SIGINT, 'interrupt'],
            [\SIGQUIT, 'quit'],
            [\SIGILL, 'illegal'],
            [\SIGTRAP, 'trap'],
            [\SIGABRT, 'abort'],
            [\SIGFPE, 'floatingPointException'],
            [\SIGBUS, 'bus'],
            [\SIGSEGV, 'segmentationViolation'],
            [\SIGSYS, 'system'],
            [\SIGPIPE, 'pipe'],
            [\SIGALRM, 'alarm'],
            [\SIGTERM, 'terminate'],
            [\SIGURG, 'urgent'],
            [\SIGTSTP, 'terminalStop'],
            [\SIGCONT, 'continue'],
            [\SIGCHLD, 'child'],
            [\SIGTTIN, 'ttyIn'],
            [\SIGTTOU, 'ttyOut'],
            [\SIGIO, 'io'],
            [\SIGXCPU, 'exceedsCpu'],
            [\SIGXFSZ, 'exceedsFileSize'],
            [\SIGVTALRM, 'virtualTimerExpired'],
            [\SIGPROF, 'profilingTimerExpired'],
            [\SIGWINCH, 'terminalWindowsSizeChanged'],
            [\SIGUSR1, 'userDefinedSignal1'],
            [\SIGUSR2, 'userDefinedSignal2'],
        ];
    }
}
