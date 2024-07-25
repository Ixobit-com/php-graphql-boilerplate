<?php

declare(strict_types=1);

namespace App\Tests\DTO;

use App\Entity\GraphQL\DTO\BaseDTO;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DTOTest extends KernelTestCase
{
    // protected ?ValidatorInterface $validator;

    public function setUp(): void
    {
        $kernel            = self::bootKernel();
        // $this->validator   = $kernel->getContainer()->get('debug.validator.test');
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
