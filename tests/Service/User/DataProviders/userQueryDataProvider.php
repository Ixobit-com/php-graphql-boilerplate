<?php

namespace App\Tests\Service\User\DataProviders;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;

trait userQueryDataProvider
{
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
                    return UserFixtures::DEFAULT_USER_LOGIN === $response->data->user->login;
                },
            ],
        ];
    }
}