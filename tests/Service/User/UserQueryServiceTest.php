<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class UserQueryServiceTest extends BaseServiceWebTestCase
{
    private const user_query = <<<EOD
query getUserInfo {
    user {
        id
        login
        roles
        profile {
            first_name
            last_name
            email
        }
    }
}
EOD;

    public function setUp(): void
    {
        parent::setUp();
    }

    #[DataProvider('provideUserData')]
    public function testUser(
        $variables,
        $expectedErrors,
        $analyzers
    ): void
    {
        $this->loginAs($variables['loginInfo']->login);
        $this->call('user', [
            'query'     => self::user_query,
            'variables' => ''
        ]);

        $response = json_decode($this->client->getResponse()->getContent());

        $this->analyzeResponse($response, $analyzers);
    }

    public static function provideUserData(): iterable
    {
        yield 'user.valid' => [
            'variables' => ['loginInfo' => new loginInputDTO(
                [
                    'login'     => UserFixtures::DEFAULT_USER_LOGIN,
                    'password'  => UserFixtures::DEFAULT_PASSWORD,
                ]
            )],
            'expectedErrors'     => [],
            'analyzers'          => [
                function (\stdClass $response) {
                    return $response->data->user->login === UserFixtures::DEFAULT_USER_LOGIN;
                },
            ]
        ];
    }

}
