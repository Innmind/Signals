<?php
declare(strict_types = 1);

namespace Innmind\Signals;

/**
 * @psalm-immutable
 */
enum Signal
{
    case hangup;
    case interrupt;
    case quit;
    case illegal;
    case trap;
    case abort;
    case floatingPointException;
    case bus;
    case segmentationViolation;
    case system;
    case pipe;
    case alarm;
    case terminate;
    case urgent;
    case terminalStop;
    case continue;
    case child;
    case ttyIn;
    case ttyOut;
    case io;
    case exceedsCpu;
    case exceedsFileSize;
    case virtualTimerExpired;
    case profilingTimerExpired;
    case terminalWindowsSizeChanged;
    case userDefinedSignal1;
    case userDefinedSignal2;

    public function toInt(): int
    {
        return match ($this) {
            self::hangup => \SIGHUP,
            self::interrupt => \SIGINT,
            self::quit => \SIGQUIT,
            self::illegal => \SIGILL,
            self::trap => \SIGTRAP,
            self::abort => \SIGABRT,
            self::floatingPointException => \SIGFPE,
            self::bus => \SIGBUS,
            self::segmentationViolation => \SIGSEGV,
            self::system => \SIGSYS,
            self::pipe => \SIGPIPE,
            self::alarm => \SIGALRM,
            self::terminate => \SIGTERM,
            self::urgent => \SIGURG,
            self::terminalStop => \SIGTSTP,
            self::continue => \SIGCONT,
            self::child => \SIGCHLD,
            self::ttyIn => \SIGTTIN,
            self::ttyOut => \SIGTTOU,
            self::io => \SIGIO,
            self::exceedsCpu => \SIGXCPU,
            self::exceedsFileSize => \SIGXFSZ,
            self::virtualTimerExpired => \SIGVTALRM,
            self::profilingTimerExpired => \SIGPROF,
            self::terminalWindowsSizeChanged => \SIGWINCH,
            self::userDefinedSignal1 => \SIGUSR1,
            self::userDefinedSignal2 => \SIGUSR2,
        };
    }
}
