# Changelog

## [Unreleased]

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
