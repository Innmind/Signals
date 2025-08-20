<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals\Signal;

use Innmind\Signals\Signal\Code;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    PHPUnit\Framework\TestCase,
    Set,
};

class CodeTest extends TestCase
{
    use BlackBox;

    public function testInterface(): BlackBox\Proof
    {
        return $this
            ->forAll(Set::integers())
            ->prove(function(int $int): void {
                $this->assertSame($int, (new Code($int))->toInt());
            });
    }
}
