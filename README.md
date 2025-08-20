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

$handler = Handler::main(); // automatically enable async signal at instanciation

$handler->listen(Signal::interrupt, function(Signal $signal, Info $info): void {
    echo 'foo';
});
$handler->listen(Signal::interrupt, function(Signal $signal, Info $info): void {
    echo 'bar';
});

// do some logic here
```

When above script is executed in a terminal and you do a `ctrl + c` to stop the process it will print `foobar` instead of stopping the script.

If for some reason you need to remove a handler (for example when a child process ended) you can call `$handler->remove($listener)` (remove the listener for all signals).
