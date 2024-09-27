<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Tests\Service\Auth\DataProviders\loginDataProvider;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class AuthQueryServiceTestLogin extends BaseServiceWebTestCase
{

    use loginDataProvider;

    /**
     * @throws \ReflectionException
     */
    #[DataProvider('provideLoginData')]
    public function testUserLogin(
        $variables,
        $expectedErrors,
        $analyzers
    ): void {
        $this->call('auth', [
            'query'     => self::auth_login_query,
            'variables' => json_encode($variables),
        ]
        );

        $response = json_decode($this->client->getResponse()->getContent());

        $this->analyzeResponseErrors(
            $response,
            $expectedErrors,
        );

        $this->analyzeResponse($response, $analyzers);
    }

}
