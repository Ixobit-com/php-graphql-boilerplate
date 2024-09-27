<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\User\Input\profileCreateInputDTO;
use App\Entity\GraphQL\DTO\User\Input\userRegistrationInputDTO;
use App\Tests\Service\Auth\DataProviders\registrationDataProvider;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class AuthMutationServiceTest extends BaseServiceWebTestCase
{

    use registrationDataProvider;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @throws \ReflectionException
     */
    #[DataProvider('provideRegistrationData')]
    public function testUserRegistration(
        $variables,
        $expectedErrors,
    ): void {
        $this->call('auth', [
            'query'     => self::auth_registration,
            'variables' => json_encode($variables),
        ]
        );

        $this->analyzeResponseErrors(
            json_decode($this->client->getResponse()->getContent()),
            $expectedErrors,
        );
    }

}
