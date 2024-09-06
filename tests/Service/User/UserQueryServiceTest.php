<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class UserQueryServiceTest extends BaseServiceWebTestCase
{
    private const user_query = <<<EOD

EOD;

    public function setUp(): void
    {
        parent::setUp();
    }


    public function testUser(): void
    {
        $this->isTrue();
    }

}
