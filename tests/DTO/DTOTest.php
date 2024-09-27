<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DTOTest extends KernelTestCase
{
    public function setUp(): void
    {
        $kernel = self::bootKernel();
        parent::setUp();
    }

    public function testBaseDTO(): void
    {
        $dto            = new BaseDTO();

        $this->assertIsArray($dto->toArray());

        $this->expectException(\InvalidArgumentException::class);
        $dto->somefield = 'value';
    }

    public function tearDown(): void
    {
        restore_exception_handler(); // some vendor(s) forgot to reset exception handler...
        parent::tearDown();
    }
}
