<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\Entity\GraphQL\DTO\User\Input\profileCreateInputDTO;
use App\Entity\GraphQL\DTO\User\Input\userRegistrationInputDTO;
use App\Entity\GraphQL\Role\FullRole;
use App\Entity\User;
use App\Service\GraphQL\Auth\AuthMutationService;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthMutationServiceTest extends KernelTestCase
{
    protected ?AuthMutationService $authService;
    protected ?ValidatorInterface $validator;
    protected ?UserPasswordHasher $hasher;

    public function setUp(): void
    {
        $kernel            = self::bootKernel();
        $this->authService = $kernel->getContainer()->get(AuthMutationService::class);
        $this->validator   = $kernel->getContainer()->get('debug.validator.test');
        $this->hasher      = $kernel->getContainer()->get('security.user_password_hasher.test');
        parent::setUp();
    }

    /**
     * @throws \ReflectionException
     */
    #[DataProvider('provideRegistrationData')]
    public function testAuthMutationService(
        $login,
        $password,
        $roles,
        $profile,
        $expectedException,
        $expectedErrors,
    ): void {
        $this->assertInstanceOf(AuthMutationService::class, $this->authService);

        $dto           = new userRegistrationInputDTO();
        $dto->login    = $login;
        $dto->password = $password;
        $dto->roles    = $roles;
        $dto->profile  = $profile();

        // In the real environment - validation supports by GraphQL library (before service call)
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->assertContains(get_class($error->getConstraint()), $expectedErrors);
            }
        }

        try {
            $user = $this->authService->userRegistration($dto);

            if (0 == count($errors)) {
                $this->assertInstanceOf(User::class, $user);
                $this->assertEquals($login, $user->getLogin());
                $this->assertTrue($this->hasher->isPasswordValid($user, $password));
                $this->assertEquals($roles, $user->getRoles());

                $this->assertEquals($dto->profile->first_name, $user->getProfile()->getFirstName());
                $this->assertEquals($dto->profile->last_name, $user->getProfile()->getLastName());
                $this->assertEquals($dto->profile->email, $user->getProfile()->getEmail());
            }
        } catch (\Throwable $e) {
            $this->assertEquals($expectedException, get_class($e));
        }
    }

    public static function provideRegistrationData(): iterable
    {
        yield 'dto.not_unique_login' => [
            'login'     => 'admin',
            'password'  => 'password',
            'roles'     => [FullRole::ROLE_DRIVER],
            'profile'   => function (): profileCreateInputDTO {
                $profile             = new profileCreateInputDTO();
                $profile->email      = 'valid@email.com';
                $profile->first_name = 'FirstName';
                $profile->last_name  = 'LastName';

                return $profile;
            },
            'expectedException'  => 'Doctrine\DBAL\Exception\UniqueConstraintViolationException',
            'expectedErrors'     => [],
        ];

        yield 'dto.not_valid_email_and_password' => [
            'login'     => 'new_driver',
            'password'  => '',
            'roles'     => [FullRole::ROLE_DRIVER],
            'profile'   => function (): profileCreateInputDTO {
                $profile             = new profileCreateInputDTO();
                $profile->email      = 'not_valid_email';
                $profile->first_name = 'FirstName';
                $profile->last_name  = 'LastName';

                return $profile;
            },
            'expectedException'  => '',
            'expectedErrors'     => ['Symfony\Component\Validator\Constraints\Email', 'Symfony\Component\Validator\Constraints\NotBlank'],
        ];

        yield 'dto.not_valid_role' => [
            'login'     => 'new_driver',
            'password'  => 'password',
            'roles'     => [FullRole::ROLE_SUPERADMIN],
            'profile'   => function (): profileCreateInputDTO {
                $profile             = new profileCreateInputDTO();
                $profile->email      = 'valid@email.com';
                $profile->first_name = 'FirstName';
                $profile->last_name  = 'LastName';

                return $profile;
            },
            'expectedException'  => '',
            'expectedErrors'     => ['Symfony\Component\Validator\Constraints\Choice'],
        ];

        yield 'dto.valid_user' => [
            'login'     => 'new_driver',
            'password'  => 'password',
            'roles'     => [FullRole::ROLE_DRIVER],
            'profile'   => function (): profileCreateInputDTO {
                $profile             = new profileCreateInputDTO();
                $profile->email      = 'valid@email.com';
                $profile->first_name = 'FirstName';
                $profile->last_name  = 'LastName';

                return $profile;
            },
            'expectedException'  => '',
            'expectedErrors'     => [],
        ];
    }

    public function tearDown(): void
    {
        restore_exception_handler(); // some vendor(s) forgot to reset exception handler...
        parent::tearDown();
    }
}
