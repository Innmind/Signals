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
            [1, 'hangup'],
            [2, 'interrupt'],
            [3, 'quit'],
            [4, 'illegal'],
            [5, 'trap'],
            [6, 'abort'],
            [8, 'floatingPointException'],
            [9, 'kill'],
            [10, 'bus'],
            [11, 'segmentationViolation'],
            [12, 'system'],
            [13, 'pipe'],
            [14, 'alarm'],
            [15, 'terminate'],
            [16, 'urgent'],
            [17, 'stop'],
            [18, 'terminalStop'],
            [19, 'continue'],
            [20, 'child'],
            [21, 'ttyIn'],
            [22, 'ttyOut'],
            [23, 'io'],
            [24, 'exceedsCpu'],
            [25, 'exceedsFileSize'],
            [26, 'virtualTimerExpired'],
            [27, 'profilingTimerExpired'],
            [28, 'terminalWindowsSizeChanged'],
            [30, 'userDefinedSignal1'],
            [31, 'userDefinedSignal2'],
        ];
    }
}
