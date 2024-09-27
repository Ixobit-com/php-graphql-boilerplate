<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth\DataProviders;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use PHPUnit\Framework\Constraint\RegularExpression;

trait loginDataProvider
{
    protected const auth_login_query = <<<EOD
query login(\$loginInfo: loginInputDTO!) {
    login(loginInfo: \$loginInfo) {
        user {
            id
            login
        }
        token
        refresh_token
    }
}
EOD;

    public static function provideLoginData(): iterable
    {
        yield 'login.valid' => [
            'variables' => ['loginInfo' => new loginInputDTO(
                [
                    'login'     => UserFixtures::DEFAULT_USER_LOGIN,
                    'password'  => UserFixtures::DEFAULT_PASSWORD,
                ]
            )],
            'expectedErrors'     => [],
            'analyzers'          => [
                function (\stdClass $response) {
                    return UserFixtures::DEFAULT_USER_LOGIN === $response->data->login->user->login;
                },
                function (\stdClass $response) {
                    return !empty($response->data->login->token);
                },
            ],
        ];

        yield 'login.invalid' => [
            'variables' => ['loginInfo' => new loginInputDTO(
                [
                    'login'     => 'notexistslogin',
                    'password'  => UserFixtures::DEFAULT_PASSWORD,
                ]
            )],
            'expectedErrors' => [
                new RegularExpression('/Invalid credentials/'),
            ],
            'analyzers'          => [],
        ];
    }
}
