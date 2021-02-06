<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals\Signal;

use Innmind\Signals\Signal\SendingProcessId;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class SendingProcessIdTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Set\Integers::any())
            ->then(function(int $int): void {
                $this->assertSame($int, (new SendingProcessId($int))->toInt());
            });
    }
}
