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
        $this->assertSame($signal, Signal::$name());
    }

    public function testEquals()
    {
        $this->assertTrue(Signal::kill()->equals(Signal::kill()));
        $this->assertFalse(Signal::kill()->equals(Signal::terminate()));
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
            [\SIGKILL, 'kill'],
            [\SIGBUS, 'bus'],
            [\SIGSEGV, 'segmentationViolation'],
            [\SIGSYS, 'system'],
            [\SIGPIPE, 'pipe'],
            [\SIGALRM, 'alarm'],
            [\SIGTERM, 'terminate'],
            [\SIGURG, 'urgent'],
            [\SIGSTOP, 'stop'],
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
