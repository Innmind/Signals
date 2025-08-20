<?php
declare(strict_types = 1);

namespace Innmind\Signals\Signal;

/**
 * @psalm-immutable
 */
final class Status
{
    private function __construct(private int $value)
    {
    }

    /**
     * @internal
     */
    public static function of(int $value): self
    {
        return new self($value);
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
