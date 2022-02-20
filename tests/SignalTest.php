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
    public function testToInt($value, $signal)
    {
        $this->assertSame($value, $signal->toInt());
    }

    public function signals(): array
    {
        return [
            [\SIGHUP, Signal::hangup],
            [\SIGINT, Signal::interrupt],
            [\SIGQUIT, Signal::quit],
            [\SIGILL, Signal::illegal],
            [\SIGTRAP, Signal::trap],
            [\SIGABRT, Signal::abort],
            [\SIGFPE, Signal::floatingPointException],
            [\SIGBUS, Signal::bus],
            [\SIGSEGV, Signal::segmentationViolation],
            [\SIGSYS, Signal::system],
            [\SIGPIPE, Signal::pipe],
            [\SIGALRM, Signal::alarm],
            [\SIGTERM, Signal::terminate],
            [\SIGURG, Signal::urgent],
            [\SIGTSTP, Signal::terminalStop],
            [\SIGCONT, Signal::continue],
            [\SIGCHLD, Signal::child],
            [\SIGTTIN, Signal::ttyIn],
            [\SIGTTOU, Signal::ttyOut],
            [\SIGIO, Signal::io],
            [\SIGXCPU, Signal::exceedsCpu],
            [\SIGXFSZ, Signal::exceedsFileSize],
            [\SIGVTALRM, Signal::virtualTimerExpired],
            [\SIGPROF, Signal::profilingTimerExpired],
            [\SIGWINCH, Signal::terminalWindowsSizeChanged],
            [\SIGUSR1, Signal::userDefinedSignal1],
            [\SIGUSR2, Signal::userDefinedSignal2],
        ];
    }
}
