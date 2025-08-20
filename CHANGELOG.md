# Changelog

## [Unreleased]

### Fixed

- Forgot to declare pure methods

## 4.1.0 - 2025-08-20

### Changed

- `Innmind\Signals\Handler` will install its handler function only when first registering a user listener

## 4.0.0 - 2025-08-20

### Added

- `Innmind\Signals\Handler::async()` (This is an internal feature for `innmind/async` that may introduce BC breaks in next minor versions)

### Changed

- These constructors are now private and internal:
    - `Innmind\Signals\Info`
    - `Innmind\Signals\Signal\Code`
    - `Innmind\Signals\Signal\ErrorNumber`
    - `Innmind\Signals\Signal\SendingProcessId`
    - `Innmind\Signals\Signal\SendingProcessUserId`
    - `Innmind\Signals\Signal\Status`
- `Innmind\Signals\Handler` constructor is now private, use `::main()` instead
- Requires `innmind/immutable:~5.18`

### Removed

- `Innmind\Signals\Handler::reset()`

## 3.1.0 - 2023-09-23

### Added

- Support for `innmind/immutable:~5.0`

### Removed

- Support for PHP `8.1`
