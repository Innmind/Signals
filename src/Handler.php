<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Map,
};

final class Handler
{
    private $handlers;
    private $ints;
    private $wasAsync;
    private $resetted = false;

    public function __construct()
    {
        $this->handlers = Map::of('int', StreamInterface::class);
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
        $this->handlers = $this->handlers->put(
            $signal->toInt(),
            $handlers->add($listener)
        );
        $this->ints = $this->ints->put($signal->toInt(), $signal);
    }

    public function remove(callable $listener): void
    {
        $this->handlers = $this
            ->handlers
            ->map(static function(int $signal, StreamInterface $listeners) use ($listener): StreamInterface {
                return $listeners->filter(static function(callable $callable) use ($listener): bool {
                    return $callable !== $listener;
                });
            })
            ->foreach(static function(int $signal, StreamInterface $listeners): void {
                if ($listeners->empty()) {
                    \pcntl_signal($signal, \SIG_DFL); // restore default handler
                }
            })
            ->filter(static function(int $signal, StreamInterface $listeners): bool {
                return !$listeners->empty();
            });
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
     * @return StreamInterface<callable>
     */
    private function install(Signal $signal): StreamInterface
    {
        if ($this->handlers->contains($signal->toInt())) {
            return $this->handlers->get($signal->toInt());;
        }

        \pcntl_signal($signal->toInt(), function(...$args): void {
            $this->dispatch(...$args);
        });

        return Stream::of('callable');
    }

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
                $structure = $structure->put('code', new Signal\Code($info['code']));
            }

            if (isset($info['errno'])) {
                $structure = $structure->put('errno', new Signal\ErrorNumber($info['errno']));
            }

            if (isset($info['pid'])) {
                $structure = $structure->put('pid', new Signal\SendingProcessId($info['pid']));
            }

            if (isset($info['uid'])) {
                $structure = $structure->put('uid', new Signal\SendingProcessUserId($info['uid']));
            }

            if (isset($info['status'])) {
                $structure = $structure->put('status', new Signal\Status($info['status']));
            }
        }

        $handlers->foreach(static function(callable $listen) use ($signal, $structure): void {
            $listen($signal, $structure);
        });
    }
}
