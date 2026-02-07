<?php
declare(strict_types = 1);

namespace Innmind\Signals\Handler;

use Innmind\Signals\{
    Signal,
    Info,
};
use Innmind\Immutable\{
    Sequence,
    Map,
    Maybe,
    Attempt,
    SideEffect,
};

/**
 * @internal
 */
final class Main
{
    /**
     * @psalm-mutation-free
     *
     * @param Map<Signal, Sequence<callable(Signal, Info): void>> $handlers
     */
    private function __construct(
        private Map $handlers,
        private bool $installed,
        private bool $wasAsync,
    ) {
    }

    /**
     * @psalm-pure
     */
    #[\NoDiscard]
    public static function install(): self
    {
        return new self(
            Map::of(),
            false,
            false,
        );
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function listen(Signal $signal, callable $listener): Attempt
    {
        if (!$this->installed) {
            $this->wasAsync = \pcntl_async_signals();
            \pcntl_async_signals(true);
            $this->installed = true;
        }

        return $this
            ->installSignal($signal)
            ->map(function($handlers) use ($signal, $listener) {
                $this->handlers = ($this->handlers)(
                    $signal,
                    ($handlers)($listener),
                );

                return SideEffect::identity;
            });
    }

    /**
     * @param callable(Signal, Info): void $listener
     *
     * @return Attempt<SideEffect>
     */
    public function remove(callable $listener): Attempt
    {
        $handlers = $this->handlers->map(
            static fn($_, $listeners) => $listeners->exclude(
                static fn($callable) => $callable === $listener,
            ),
        );

        return $handlers
            ->toSequence()
            ->sink(SideEffect::identity)
            ->attempt(static function($_, $installed) {
                if ($installed->value()->empty()) {
                    $uninstalled = \pcntl_signal(
                        $installed->key()->toInt(),
                        \SIG_DFL,
                    ); // restore default handler

                    if (!$uninstalled) {
                        return Attempt::error(new \RuntimeException('Failed to restore default handler'));
                    }
                }

                return Attempt::result($_);
            })
            ->map(function($_) use ($handlers) {
                $this->handlers = $handlers->exclude(
                    static fn($_, $listeners) => $listeners->empty(),
                );

                return $_;
            })
            ->map(function($_) {
                if ($this->handlers->empty()) {
                    $this->installed = false;
                    \pcntl_async_signals($this->wasAsync);
                }

                return $_;
            });
    }

    /**
     * @return Attempt<Sequence<callable(Signal, Info): void>>
     */
    private function installSignal(Signal $signal): Attempt
    {
        return $this
            ->handlers
            ->get($signal)
            ->attempt(static fn() => new \Exception('Signal not installed'))
            ->recover(function() use ($signal) {
                $installed = \pcntl_signal($signal->toInt(), function($signo, $siginfo): void {
                    $this->dispatch(Signal::of($signo), $siginfo);
                });

                if (!$installed) {
                    return Attempt::error(new \RuntimeException('Failed to install signal listener'));
                }

                return Attempt::result(Sequence::of());
            });
    }

    private function dispatch(Signal $signal, mixed $info): void
    {
        $info = \is_array($info) ? $info : [];
        $structure = Info::of(
            Maybe::of($info['code'] ?? null)->map(Signal\Code::of(...)),
            Maybe::of($info['errno'] ?? null)->map(Signal\ErrorNumber::of(...)),
            Maybe::of($info['pid'] ?? null)->map(Signal\SendingProcessId::of(...)),
            Maybe::of($info['uid'] ?? null)->map(Signal\SendingProcessUserId::of(...)),
            Maybe::of($info['status'] ?? null)->map(Signal\Status::of(...)),
        );

        $_ = $this
            ->handlers
            ->get($signal)
            ->toSequence()
            ->flatMap(static fn($listeners) => $listeners)
            ->foreach(static fn($listener) => $listener($signal, $structure));
    }
}
