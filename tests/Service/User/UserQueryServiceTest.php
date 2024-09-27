<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\Entity\GraphQL\DTO\Common\paginationInputDTO;
use App\Tests\Service\BaseServiceWebTestCase;
use App\Tests\Service\User\DataProviders\userQueryDataProvider;
use App\Tests\Service\User\DataProviders\usersQueryDataProvider;
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

    use userQueryDataProvider;

    #[DataProvider('provideUserData')]
    public function testUser(
        $variables,
        $expectedErrors,
        $analyzers
    ): void {
        $this->loginAs($variables['loginInfo']->login);
        $this->call('user', [
            'query'     => self::user_query,
            'variables' => '',
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->analyzeResponse($response, $analyzers);
    }


    use usersQueryDataProvider;

    #[DataProvider('provideUsersData')]
    public function testUsers(
        $variables,
        $expectedErrors,
        $analyzers
    ): void {
        $pagination = new paginationInputDTO();
        $this->loginAs($variables['loginInfo']->login);
        $this->call('user', [
            'query'     => self::users_query,
            'variables' => sprintf(
                '{ "pagination": %s }',
                $pagination->toJson()
            ),
        ]);
        $response = json_decode($this->client->getResponse()->getContent());

        $this->analyzeResponseErrors($response, $expectedErrors);
        $this->analyzeResponse($response, $analyzers, ['pagination' => $pagination]);
    }

}
