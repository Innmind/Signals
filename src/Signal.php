<?php
declare(strict_types = 1);

namespace Innmind\SignalHandler;

final class Signal
{
    private static $hangup;
    private static $interrupt;
    private static $quit;
    private static $illegal;
    private static $trap;
    private static $abort;
    private static $floatingPointException;
    private static $kill;
    private static $bus;
    private static $segmentationViolation;
    private static $system;
    private static $pipe;
    private static $alarm;
    private static $terminate;
    private static $urgent;
    private static $stop;
    private static $terminalStop;
    private static $continue;
    private static $child;
    private static $ttyIn;
    private static $ttyOut;
    private static $io;
    private static $exceedsCpu;
    private static $exceedsFileSize;
    private static $virtualTimerExpired;
    private static $profilingTimerExpired;
    private static $terminalWindowsSizeChanged;
    private static $userDefinedSignal1;
    private static $userDefinedSignal2;

    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function hangup(): self
    {
        return self::$hangup ?? self::$hangup = new self(\SIGHUP);
    }

    public static function interrupt(): self
    {
        return self::$interrupt ?? self::$interrupt = new self(\SIGINT);
    }

    public static function quit(): self
    {
        return self::$quit ?? self::$quit = new self(\SIGQUIT);
    }

    public static function illegal(): self
    {
        return self::$illegal ?? self::$illegal = new self(\SIGILL);
    }

    public static function trap(): self
    {
        return self::$trap ?? self::$trap = new self(\SIGTRAP);
    }

    public static function abort(): self
    {
        return self::$abort ?? self::$abort = new self(\SIGABRT);
    }

    public static function floatingPointException(): self
    {
        return self::$floatingPointException ?? self::$floatingPointException = new self(\SIGFPE);
    }

    public static function kill(): self
    {
        return self::$kill ?? self::$kill = new self(\SIGKILL);
    }

    public static function bus(): self
    {
        return self::$bus ?? self::$bus = new self(\SIGBUS);
    }

    public static function segmentationViolation(): self
    {
        return self::$segmentationViolation ?? self::$segmentationViolation = new self(\SIGSEGV);
    }

    public static function system(): self
    {
        return self::$system ?? self::$system = new self(\SIGSYS);
    }

    public static function pipe(): self
    {
        return self::$pipe ?? self::$pipe = new self(\SIGPIPE);
    }

    public static function alarm(): self
    {
        return self::$alarm ?? self::$alarm = new self(\SIGALRM);
    }

    public static function terminate(): self
    {
        return self::$terminate ?? self::$terminate = new self(\SIGTERM);
    }

    public static function urgent(): self
    {
        return self::$urgent ?? self::$urgent = new self(\SIGURG);
    }

    public static function stop(): self
    {
        return self::$stop ?? self::$stop = new self(\SIGSTOP);
    }

    public static function terminalStop(): self
    {
        return self::$terminalStop ?? self::$terminalStop = new self(\SIGTSTP);
    }

    public static function continue(): self
    {
        return self::$continue ?? self::$continue = new self(\SIGCONT);
    }

    public static function child(): self
    {
        return self::$child ?? self::$child = new self(\SIGCHLD);
    }

    public static function ttyIn(): self
    {
        return self::$ttyIn ?? self::$ttyIn = new self(\SIGTTIN);
    }

    public static function ttyOut(): self
    {
        return self::$ttyOut ?? self::$ttyOut = new self(\SIGTTOU);
    }

    public static function io(): self
    {
        return self::$io ?? self::$io = new self(\SIGIO);
    }

    public static function exceedsCpu(): self
    {
        return self::$exceedsCpu ?? self::$exceedsCpu = new self(\SIGXCPU);
    }

    public static function exceedsFileSize(): self
    {
        return self::$exceedsFileSize ?? self::$exceedsFileSize = new self(\SIGXFSZ);
    }

    public static function virtualTimerExpired(): self
    {
        return self::$virtualTimerExpired ?? self::$virtualTimerExpired = new self(\SIGVTALRM);
    }

    public static function profilingTimerExpired(): self
    {
        return self::$profilingTimerExpired ?? self::$profilingTimerExpired = new self(\SIGPROF);
    }

    public static function terminalWindowsSizeChanged(): self
    {
        return self::$terminalWindowsSizeChanged ?? self::$terminalWindowsSizeChanged = new self(\SIGWINCH);
    }

    public static function userDefinedSignal1(): self
    {
        return self::$userDefinedSignal1 ?? self::$userDefinedSignal1 = new self(\SIGUSR1);
    }

    public static function userDefinedSignal2(): self
    {
        return self::$userDefinedSignal2 ?? self::$userDefinedSignal2 = new self(\SIGUSR2);
    }

    public function equals(self $signal): bool
    {
        return $this->value === $signal->value;
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
