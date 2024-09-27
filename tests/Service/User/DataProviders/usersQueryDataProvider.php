<?php

namespace App\Tests\Service\User\DataProviders;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;

trait usersQueryDataProvider
{
    protected const users_query = <<<EOD
query getUserInfo(\$pagination: paginationInputDTO) {
    users(pagination: \$pagination) {
        users {
            id
            login
            roles
            profile {
                first_name
                last_name
                email
            }
        },
        limit
        offset
        total
    }
}
EOD;

    public static function provideUsersData(): iterable
    {
        yield 'users.login.invalid' => [
            'variables' => [
                'loginInfo' => new loginInputDTO(
                    [
                        'login'     => UserFixtures::DEFAULT_USER_LOGIN,
                        'password'  => UserFixtures::DEFAULT_PASSWORD,
                    ]
                )
            ],
            'expectedErrors'     => [
                'Access denied to this field'
            ],
            'analyzers'          => [],
        ];
        yield 'users.login.valid' => [
            'variables' => [
                'loginInfo' => new loginInputDTO(
                    [
                        'login'     => UserFixtures::DEFAULT_ADMIN_LOGIN,
                        'password'  => UserFixtures::DEFAULT_PASSWORD,
                    ]
                )
            ],
            'expectedErrors'     => [],
            'analyzers'          => [
                function (\stdClass $response, array $context) {
                    return
                        is_array($response->data->users->users) and
                        count($response->data->users->users) === $context['pagination']->limit
                    ;
                },
            ],
        ];
    }
}