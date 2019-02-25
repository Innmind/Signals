# Signals

| `develop` |
|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/DependencyGraph/build-status/develop) |

Small abstraction on top of `pcntl_signal` to allow to register multiple callables for a single signal.

## Installation

```sh
composer require innmind/signals
```

## Usage

```php
use Innmind\Signals\{
    Handler,
    Signal,
};
use Innmind\Immutable\MapInterface;

$handler = new Handler; // automatically enable async signal at instanciation

$handler->listen(Signal::interrupt(), function(Signal $signal, MapInterface $info): void {
    echo 'foo';
});
$handler->listen(Signal::interrupt(), function(Signal $signal, MapInterface $info): void {
    echo 'bar';
});

// do some logic here
```

When above script is executed in a terminal and you do a `ctrl + c` to stop the process it will print `foobar` instead of stopping the script.

**Important**: when using handlers in a program that use `pcntl_fork`, remember to reset the handler via `$handler->reset()` in the child process to avoid both processes to call the listeners. Once resetted a handler can no longer be used, you need to build a new instance of it.

If for some reason you need to remove a handler (for example when a child process ended) you can call `$handler->remove($listener)` (remove the listener for all signals).
