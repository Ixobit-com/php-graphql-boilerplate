<?php

declare(strict_types=1);

namespace App\Service\GraphQL\Auth;

use App\Entity\GraphQL\DTO\Auth\Input\loginInputDTO;
use App\Entity\GraphQL\DTO\Auth\Input\refreshInputDTO;
use App\Entity\GraphQL\DTO\Auth\Output\loginResponseDTO;
use App\Entity\GraphQL\DTO\Auth\Output\refreshResponseDTO;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Overblog\GraphQLBundle\Annotation as GQL;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthQuery')]
#[GQL\Access("hasRole('PUBLIC_ACCESS')")]
#[WithMonologChannel('security')]
class AuthQueryService extends BaseGraphQLService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected UserPasswordHasherInterface $passwordHasher,
        protected JWTTokenManagerInterface $JWTManager,
        protected LoggerInterface $logger,
        protected RefreshTokenGeneratorInterface $refreshTokenGenerator,
        protected ParameterBagInterface $configuration,
        protected TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    /**
     * Authorization.
     * Provide JWT token and JWT refresh token by login/password combination.
     */
    #[GQL\Field(type: 'loginResponseDTO')]
    #[GQL\Arg(name: 'loginInfo', type: 'loginInputDTO')]
    public function login(loginInputDTO $loginInfo): ?loginResponseDTO
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $loginInfo->login]);
        if (!$user instanceof UserInterface) {
            $this->logger->error(
                sprintf(
                    "User '%s' not found",
                    $loginInfo->login
                )
            );
            throw new AuthenticationException('Invalid credentials');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $loginInfo->password)) {
            $this->logger->error(
                sprintf("User '%s' provide invalid password: '%s'",
                    $user->getUserIdentifier(),
                    $loginInfo->password
                )
            );

            throw new AuthenticationException('Invalid credentials');
        }

        $token = $this->JWTManager->create($user);

        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);
        if (!$refreshToken instanceof RefreshToken or !$refreshToken->isValid()) { // refresh token is not exists or invalid
            $refreshToken = $this->generateRefreshToken($user, $refreshToken);
        }

        $response                   = new loginResponseDTO();
        $response->refresh_token    = $refreshToken->getRefreshToken();
        $response->user             = $user;
        $response->token            = $token;

        $this->logger->info(
            sprintf(
                "Provided new JWT token for user '%s'",
                $user->getUserIdentifier()
            )
        );

        return $response;
    }

    /**
     * Refresh JWT token by JWT refresh token.
     * Refresh token will be regenerated.
     */
    #[GQL\Field(type: 'refreshResponseDTO')]
    #[GQL\Arg(name: 'refreshInfo', type: 'refreshInputDTO')]
    public function refresh(refreshInputDTO $refreshInfo): ?refreshResponseDTO
    {
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $refreshInfo->refresh_token]);

        if (!$refreshToken instanceof RefreshToken) {
            $this->logger->error(
                sprintf(
                    "Provided refresh token is not exists: '%s'",
                    $refreshInfo->refresh_token
                )
            );
            throw new InvalidTokenException('Invalid refresh token');
        }

        if ($refreshToken->isValid()) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $refreshToken->getUsername()]);

            $token           = $this->JWTManager->create($user);
            $newRefreshToken = $this->generateRefreshToken($user, $refreshToken);
        } else {
            $this->logger->error(
                sprintf(
                    "User '%s' provide invalid refresh token: '%s'; Expired at: '%s'",
                    $refreshToken->getUsername(),
                    $refreshToken->getRefreshToken(),
                    $refreshToken->getValid()->format('c')
                )
            );
            throw new InvalidTokenException('Invalid refresh token');
        }

        $response                   = new refreshResponseDTO();
        $response->token            = $token;
        $response->refresh_token    = $newRefreshToken->getRefreshToken();

        $this->logger->info(
            sprintf(
                "User '%s' obtain new JWT token",
                $refreshToken->getUsername(),
            )
        );

        return $response;
    }

    /**
     * Generate|Regenerate refresh token.
     */
    private function generateRefreshToken(User $user, ?RefreshToken $refreshToken = null): RefreshTokenInterface
    {
        $refresh_token_ttl = $this->configuration->get('gesdinet_jwt_refresh_token.ttl');

        if ($refreshToken instanceof RefreshToken) {
            $this->entityManager->remove($refreshToken); // remove old refresh_token
        }

        $refresh_token = $this->refreshTokenGenerator->createForUserWithTtl($user, $refresh_token_ttl);
        $this->entityManager->persist($refresh_token);
        $this->entityManager->flush();

        $this->logger->info(
            sprintf(
                "User '%s' obtain new JWT refresh token",
                $refresh_token->getUsername(),
            )
        );

        return $refresh_token;
    }
}
