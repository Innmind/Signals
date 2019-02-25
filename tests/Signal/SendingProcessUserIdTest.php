<?php
declare(strict_types = 1);

namespace Tests\Innmind\Signals\Signal;

use Innmind\Signals\Signal\SendingProcessUserId;
use PHPUnit\Framework\TestCase;
use Eris\{
    Generator,
    TestTrait,
};

class SendingProcessUserIdTest extends TestCase
{
    use TestTrait;

    public function testInterface()
    {
        $this
            ->forAll(Generator\int())
            ->then(function(int $int): void {
                $this->assertSame($int, (new SendingProcessUserId($int))->toInt());
            });
    }
}
