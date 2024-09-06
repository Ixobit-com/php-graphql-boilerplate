<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class AuthQueryServiceTestLogin extends BaseServiceWebTestCase
{
    private const auth_login_query = <<<EOD
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

    public function setUp(): void
    {
        parent::setUp();
    }

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
                    return $response->data->login->user->login === UserFixtures::DEFAULT_USER_LOGIN;
                },
                function (\stdClass $response) {
                    return !empty($response->data->login->token);
                },
            ]
        ];

        yield 'login.invalid' => [
            'variables' => ['loginInfo' => new loginInputDTO(
                [
                    'login'     => 'notexistslogin',
                    'password'  => UserFixtures::DEFAULT_PASSWORD,
                ]
            )],
            'expectedErrors' => [
                new RegularExpression('/Invalid credentials/')
            ],
            'analyzers'          => []
        ];
    }
}
