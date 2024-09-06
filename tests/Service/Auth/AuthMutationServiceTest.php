<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\DataFixtures\UserFixtures;
use App\Entity\GraphQL\DTO\User\Input\profileCreateInputDTO;
use App\Entity\GraphQL\DTO\User\Input\userRegistrationInputDTO;
use App\Tests\Service\BaseServiceWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\RegularExpression;

class AuthMutationServiceTest extends BaseServiceWebTestCase
{
    private const auth_registration = <<<EOD
mutation userRegistration(\$user: userRegistrationInputDTO!)
{
    userRegistration(user: \$user) {
        id
        login
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
    #[DataProvider('provideRegistrationData')]
    public function testUserRegistration(
        $variables,
        $expectedErrors,
    ): void {
        $this->call('auth', [
            'query'     => self::auth_registration,
            'variables' => json_encode($variables),
        ]
        );

        $this->analyzeResponseErrors(
            json_decode($this->client->getResponse()->getContent()),
            $expectedErrors,
        );
    }

    public static function provideRegistrationData(): iterable
    {
        yield 'registration.valid' => [
            'variables' => ['user' => new userRegistrationInputDTO(
                [
                    'login'     => 'newuserlogin',
                    'password'  => 'password',
                    'profile'   => new profileCreateInputDTO([
                        'email'      => 'valid@email.com',
                        'first_name' => 'FirstName',
                        'last_name'  => 'LastName',
                    ]),
                ]
            ),
            ],
            'expectedErrors'     => [],
        ];

        yield 'registration.login_already_exists-password-required' => [
            'variables' => ['user' => new userRegistrationInputDTO(
                [
                    'login'     => UserFixtures::DEFAULT_USER_LOGIN,
                    'password'  => '',
                    'profile'   => new profileCreateInputDTO([
                        'email'      => 'invalid_email_com',
                        'first_name' => 'FirstName',
                        'last_name'  => 'LastName',
                    ]),
                ]
            ),
            ],
            'expectedErrors' => [
                new RegularExpression('/Login ".*" already exists/'),
                new RegularExpression('/Password is required/'),
                new RegularExpression('/Provided email ".*" is not valid email address/'),
            ],
        ];
    }
}
