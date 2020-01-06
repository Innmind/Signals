<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\{
    Sequence,
    Map,
};

final class Handler
{
    /** @var Map<int, Sequence<callable>> */
    private Map $handlers;
    /** @var Map<int, Signal> */
    private Map $ints;
    private bool $wasAsync;
    private bool $resetted = false;

    public function __construct()
    {
        /** @var Map<int, Sequence<callable>> */
        $this->handlers = Map::of('int', Sequence::class);
        /** @var Map<int, Signal> */
        $this->ints = Map::of('int', Signal::class);
        $this->wasAsync = \pcntl_async_signals();
        \pcntl_async_signals(true);
    }

    public function listen(Signal $signal, callable $listener): void
    {
        if ($this->resetted) {
            return;
        }

        $handlers = $this->install($signal);
        $this->handlers = ($this->handlers)(
            $signal->toInt(),
            ($handlers)($listener)
        );
        $this->ints = ($this->ints)($signal->toInt(), $signal);
    }

    public function remove(callable $listener): void
    {
        $handlers = $this->handlers->map(
            static fn(int $signal, Sequence $listeners): Sequence => $listeners->filter(
                static fn(callable $callable): bool => $callable !== $listener,
            ),
        );
        $handlers->foreach(static function(int $signal, Sequence $listeners): void {
            if ($listeners->empty()) {
                \pcntl_signal($signal, \SIG_DFL); // restore default handler
            }
        });
        $this->handlers = $handlers->filter(
            static fn(int $signal, Sequence $listeners): bool => !$listeners->empty()
        );
    }

    public function reset(): void
    {
        $this->handlers->foreach(static function(int $signal): void {
            \pcntl_signal($signal, \SIG_DFL);
        });
        \pcntl_async_signals($this->wasAsync);
        $this->handlers = $this->handlers->clear();
        $this->ints = $this->ints->clear();
        $this->resetted = true;
    }

    /**
     * @return Sequence<callable>
     */
    private function install(Signal $signal): Sequence
    {
        if ($this->handlers->contains($signal->toInt())) {
            return $this->handlers->get($signal->toInt());;
        }

        /** @psalm-suppress MissingClosureParamType */
        \pcntl_signal($signal->toInt(), function(...$args): void {
            /** @psalm-suppress MixedArgument */
            $this->dispatch(...$args);
        });

        /** @var Sequence<callable> */
        return Sequence::of('callable');
    }

    /**
     * @param mixed $info
     */
    private function dispatch(int $signal, $info): void
    {
        if (!$this->handlers->contains($signal)) {
            return;
        }

        $handlers = $this->handlers->get($signal);
        $signal = $this->ints->get($signal);
        $structure = Map::of('string', 'object');

        if (\is_array($info)) {
            if (isset($info['code'])) {
                /** @psalm-suppress MixedArgument */
                $structure = ($structure)('code', new Signal\Code($info['code']));
            }

            if (isset($info['errno'])) {
                /** @psalm-suppress MixedArgument */
                $structure = ($structure)('errno', new Signal\ErrorNumber($info['errno']));
            }

            if (isset($info['pid'])) {
                /** @psalm-suppress MixedArgument */
                $structure = ($structure)('pid', new Signal\SendingProcessId($info['pid']));
            }

            if (isset($info['uid'])) {
                /** @psalm-suppress MixedArgument */
                $structure = ($structure)('uid', new Signal\SendingProcessUserId($info['uid']));
            }

            if (isset($info['status'])) {
                /** @psalm-suppress MixedArgument */
                $structure = ($structure)('status', new Signal\Status($info['status']));
            }
        }

        $handlers->foreach(static function(callable $listen) use ($signal, $structure): void {
            $listen($signal, $structure);
        });
    }
}
