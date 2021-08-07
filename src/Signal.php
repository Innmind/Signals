<?php
declare(strict_types = 1);

namespace Innmind\Signals;

/**
 * @psalm-immutable
 */
final class Signal
{
    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public static function hangup(): self
    {
        return new self(\SIGHUP);
    }

    public static function interrupt(): self
    {
        return new self(\SIGINT);
    }

    public static function quit(): self
    {
        return new self(\SIGQUIT);
    }

    public static function illegal(): self
    {
        return new self(\SIGILL);
    }

    public static function trap(): self
    {
        return new self(\SIGTRAP);
    }

    public static function abort(): self
    {
        return new self(\SIGABRT);
    }

    public static function floatingPointException(): self
    {
        return new self(\SIGFPE);
    }

    public static function bus(): self
    {
        return new self(\SIGBUS);
    }

    public static function segmentationViolation(): self
    {
        return new self(\SIGSEGV);
    }

    public static function system(): self
    {
        return new self(\SIGSYS);
    }

    public static function pipe(): self
    {
        return new self(\SIGPIPE);
    }

    public static function alarm(): self
    {
        return new self(\SIGALRM);
    }

    public static function terminate(): self
    {
        return new self(\SIGTERM);
    }

    public static function urgent(): self
    {
        return new self(\SIGURG);
    }

    public static function terminalStop(): self
    {
        return new self(\SIGTSTP);
    }

    public static function continue(): self
    {
        return new self(\SIGCONT);
    }

    public static function child(): self
    {
        return new self(\SIGCHLD);
    }

    public static function ttyIn(): self
    {
        return new self(\SIGTTIN);
    }

    public static function ttyOut(): self
    {
        return new self(\SIGTTOU);
    }

    public static function io(): self
    {
        return new self(\SIGIO);
    }

    public static function exceedsCpu(): self
    {
        return new self(\SIGXCPU);
    }

    public static function exceedsFileSize(): self
    {
        return new self(\SIGXFSZ);
    }

    public static function virtualTimerExpired(): self
    {
        return new self(\SIGVTALRM);
    }

    public static function profilingTimerExpired(): self
    {
        return new self(\SIGPROF);
    }

    public static function terminalWindowsSizeChanged(): self
    {
        return new self(\SIGWINCH);
    }

    public static function userDefinedSignal1(): self
    {
        return new self(\SIGUSR1);
    }

    public static function userDefinedSignal2(): self
    {
        return new self(\SIGUSR2);
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
