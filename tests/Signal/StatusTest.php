<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals\Signal;

use Innmind\Signals\Signal\Status;
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class StatusTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\int())
            ->then(function(int $int): void {
                $this->assertSame($int, (new Status($int))->toInt());
            });
    }
}
