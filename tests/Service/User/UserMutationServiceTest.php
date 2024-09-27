<?php

declare(strict_types=1);

namespace App\Tests\Service\User;

use App\Tests\Service\BaseServiceWebTestCase;
use App\Tests\Service\User\DataProviders\userMutationDataProvider;
use PHPUnit\Framework\Attributes\DataProvider;

class UserMutationServiceTest extends BaseServiceWebTestCase
{

    use userMutationDataProvider;

    #[DataProvider('provideUserData')]
    public function testUser(
        $login,
        $variables,
        $expectedErrors,
        $analyzers
    ): void {
        $this->loginAs($login);
        $this->call('user', [
            'query'     => self::user_mutation,
            'variables' => '{ "login":"'.$variables['login'].'", "user": '.$variables['userInfo']->toJson().' }',
        ]);

        $response = json_decode($this->client->getResponse()->getContent());

        $this->analyzeResponse($response, $analyzers);
    }

}
