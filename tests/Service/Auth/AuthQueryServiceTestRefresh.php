<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\Auth\Input\refreshInputDTO;
use App\Entity\RefreshToken;
use App\Tests\Service\BaseServiceWebTestCase;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class AuthQueryServiceTestRefresh extends BaseServiceWebTestCase
{

    private ?EntityManager $entityManager;
    private const auth_refresh_query = <<<EOD
query refresh(\$refreshInfo: refreshInputDTO!) {
    refresh(refreshInfo: \$refreshInfo) {
        token
        refresh_token
    }
}
EOD;

    public function setUp(): void
    {
        parent::setUp();
        $this->entityManager = static::getContainer()->get('doctrine.orm.default_entity_manager');
    }

    #[DataProvider('provideRefreshData')]
    public function testUserRefresh(
        $variables,
        $expectedErrors,
        $analyzers
    ): void {
        $this->call('auth', [
            'query'     => self::auth_refresh_query,
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

    public function testUserRefreshValid(): void {

        $loginResponse = $this->loginAs(UserFixtures::DEFAULT_USER_LOGIN);

        $variables = ['refreshInfo' => new refreshInputDTO(
            [
                'refresh_token' => $loginResponse->data->login->refresh_token,
            ]
        )];
        $this->call('auth', [
                'query'     => self::auth_refresh_query,
                'variables' => json_encode($variables),
            ]
        );

        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(
            $response->data->refresh->refresh_token,
            $this->entityManager->getRepository(RefreshToken::class)
            ->findOneBy(['username' => UserFixtures::DEFAULT_USER_LOGIN])
            ->getRefreshToken()
        );
    }

    public static function provideRefreshData(): iterable
    {
        yield 'refresh.valid' => [
            'variables' => ['refreshInfo' => new refreshInputDTO(
                [
                    'refresh_token' => 'example-refresh-token',
                ]
            )],
            'expectedErrors'     => [
                new RegularExpression('/Invalid refresh token/')
            ],
            'analyzers'          => []
        ];
    }
}
