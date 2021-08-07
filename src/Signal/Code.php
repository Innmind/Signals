<?php
declare(strict_types = 1);

namespace Innmind\Signals\Signal;

/**
 * @psalm-immutable
 */
final class Code
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
