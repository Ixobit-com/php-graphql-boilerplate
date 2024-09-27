<?php

declare(strict_types=1);

namespace App\Tests\Service\User\DataProviders;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\User\Input\profileUpdateInputDTO;
use App\Entity\GraphQL\DTO\User\Input\userUpdateInputDTO;

trait userMutationDataProvider
{
    protected const user_mutation = <<<EOD
mutation updateUser(\$login: String!, \$user: userUpdateInputDTO!)
{
    userUpdate(login: \$login, user: \$user) {
        login
        profile {
            first_name
            last_name
            email
        }
    }
}
EOD;

    public static function provideUserData(): iterable
    {
        yield 'user-update.valid' => [
            'login'     => UserFixtures::DEFAULT_USER_LOGIN,
            'variables' => [
                'login'    => UserFixtures::DEFAULT_USER_LOGIN,
                'userInfo' => new userUpdateInputDTO(
                    [
                        'profile'   => new profileUpdateInputDTO(
                            [
                                'email' => 'new-email@example.com',
                            ]
                        ),
                    ]
                )],
            'expectedErrors'     => [],
            'analyzers'          => [
                function (\stdClass $response) {
                    return 'new-email@example.com' === $response->data->userUpdate->profile->email;
                },
            ],
        ];
        yield 'user-update.change-login' => [
            'login'     => UserFixtures::DEFAULT_USER_LOGIN,
            'variables' => [
                'login'    => UserFixtures::DEFAULT_USER_LOGIN,
                'userInfo' => new userUpdateInputDTO(
                    [
                        'login'      => 'newlogin',
                        'password'   => 'newpassword',
                    ]
                )],
            'expectedErrors'     => [],
            'analyzers'          => [
                function (\stdClass $response) {
                    return 'newlogin' === $response->data->userUpdate->login;
                },
            ],
        ];
    }
}
