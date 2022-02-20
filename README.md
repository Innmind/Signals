# Signals

[![Build Status](https://github.com/innmind/signals/workflows/CI/badge.svg?branch=master)](https://github.com/innmind/signals/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/innmind/signals/branch/develop/graph/badge.svg)](https://codecov.io/gh/innmind/signals)
[![Type Coverage](https://shepherd.dev/github/innmind/signals/coverage.svg)](https://shepherd.dev/github/innmind/signals)

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
    Info,
};

$handler = new Handler; // automatically enable async signal at instanciation

$handler->listen(Signal::interrupt, function(Signal $signal, Info $info): void {
    echo 'foo';
});
$handler->listen(Signal::interrupt, function(Signal $signal, Info $info): void {
    echo 'bar';
});

// do some logic here
```

When above script is executed in a terminal and you do a `ctrl + c` to stop the process it will print `foobar` instead of stopping the script.

**Important**: when using handlers in a program that use `pcntl_fork`, remember to reset the handler via `$handler->reset()` in the child process to avoid both processes to call the listeners. Once resetted a handler can no longer be used, you need to build a new instance of it.

If for some reason you need to remove a handler (for example when a child process ended) you can call `$handler->remove($listener)` (remove the listener for all signals).
