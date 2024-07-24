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
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Exception\InvalidTokenException;
use Monolog\Attribute\WithMonologChannel;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthQuery')]
#[GQL\Access("hasRole('PUBLIC_ACCESS')")]
#[WithMonologChannel('security')]
class AuthQueryService extends BaseGraphQLService
{
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
            throw new AuthenticationException("User '{$loginInfo->login}' not found");
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

        $response     = new loginResponseDTO();
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);
        if (!$refreshToken instanceof RefreshToken) { // refresh token is not exists
            $refreshToken = $this->generateRefreshToken($user);
        }
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
            if (!$user instanceof UserInterface) {
                $this->logger->error(
                    sprintf(
                        "User '%s' for refresh token '%s' is not found",
                        $refreshToken->getUsername(),
                        $refreshInfo->refresh_token
                    )
                );
                throw new AuthenticationException('User for this refresh token is not found');
            }

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
