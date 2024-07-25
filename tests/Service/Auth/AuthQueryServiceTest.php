<?php

declare(strict_types=1);

namespace App\Tests\Service\Auth;

use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Entity\GraphQL\DTO\Auth\Input\refreshInputDTO;
use App\Entity\GraphQL\DTO\Auth\Output\loginResponseDTO;
use App\Entity\RefreshToken;
use App\Service\GraphQL\Auth\AuthQueryService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthQueryServiceTest extends KernelTestCase
{
    protected ?AuthQueryService $authService;
    protected ?JWTTokenManagerInterface $jwtManager;
    protected ?ValidatorInterface $validator;
    protected ?EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        $kernel              = self::bootKernel();
        $this->authService   = $kernel->getContainer()->get(AuthQueryService::class);
        $this->jwtManager    = $kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $this->validator     = $kernel->getContainer()->get('debug.validator.test');
        $this->entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager.test');
        parent::setUp();
    }

    #[DataProvider('provideAuthorizationData')]
    public function testLogin(
        $login,
        $password,
        $expectedException,
        $expectedErrors,
    ): void {
        $this->assertInstanceOf(AuthQueryService::class, $this->authService);

        $dto           = new loginInputDTO();
        $dto->login    = $login;
        $dto->password = $password;

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->assertContains(get_class($error->getConstraint()), $expectedErrors);
            }
        }

        try {
            $response      = $this->authService->login($dto);

            if (0 == count($errors)) {
                $this->assertInstanceOf(loginResponseDTO::class, $response);
                $token = $this->jwtManager->parse($response->token);
                $this->assertEquals($dto->login, $token['username']);

                /** @var RefreshToken $refreshToken */
                $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $response->refresh_token]);
                $this->assertTrue($refreshToken->isValid());
                $this->assertEquals($dto->login, $refreshToken->getUsername());
            }
        } catch (\Throwable $e) {
            $this->assertEquals($expectedException, get_class($e));
        }
    }

    public function testRefresh()
    {
        $dto           = new loginInputDTO();
        $dto->login    = 'admin';
        $dto->password = 'password';

        // Get valid token
        $response      = $this->authService->login($dto);

        $refresh_dto                = new refreshInputDTO();
        $refresh_dto->refresh_token = $response->refresh_token;

        $response      = $this->authService->refresh($refresh_dto);

        /** @var RefreshToken $refreshToken */
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $response->refresh_token]);
        $this->assertTrue($refreshToken->isValid());
        $this->assertEquals($dto->login, $refreshToken->getUsername());

        // Try expired token
        $refreshToken->setValid(\DateTimeImmutable::createFromFormat('Y-m-d', '2015-09-34'));
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();
        $refresh_dto                = new refreshInputDTO();
        $refresh_dto->refresh_token = $refreshToken->getRefreshToken();

        try {
            $response = $this->authService->refresh($refresh_dto);
        } catch (InvalidTokenException $e) {
            $this->assertTrue(true);
        }

        // Try non exists token
        $refresh_dto                = new refreshInputDTO();
        $refresh_dto->refresh_token = 'wrong_token';
        try {
            $response = $this->authService->refresh($refresh_dto);
        } catch (InvalidTokenException $e) {
            $this->assertTrue(true);
        }
    }

    public static function provideAuthorizationData(): iterable
    {
        yield 'dto.empty_login' => [
            'login'              => '',
            'password'           => 'password',
            'expectedException'  => 'Symfony\Component\Security\Core\Exception\AuthenticationException',
            'expectedErrors'     => ['Symfony\Component\Validator\Constraints\NotBlank'],
        ];

        yield 'dto.empty_password' => [
            'login'              => 'admin',
            'password'           => '',
            'expectedException'  => 'Symfony\Component\Security\Core\Exception\AuthenticationException',
            'expectedErrors'     => ['Symfony\Component\Validator\Constraints\NotBlank'],
        ];

        yield 'dto.wrong_login' => [
            'login'              => 'wrong_admin',
            'password'           => 'password',
            'expectedException'  => 'Symfony\Component\Security\Core\Exception\AuthenticationException',
            'expectedErrors'     => [],
        ];

        yield 'dto.wrong_password' => [
            'login'              => 'admin',
            'password'           => 'wrong_password',
            'expectedException'  => 'Symfony\Component\Security\Core\Exception\AuthenticationException',
            'expectedErrors'     => [],
        ];

        yield 'dto.success_login' => [
            'login'              => 'admin',
            'password'           => 'password',
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
