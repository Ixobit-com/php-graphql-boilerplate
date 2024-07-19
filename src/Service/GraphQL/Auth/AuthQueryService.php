<?php

namespace App\Service\GraphQL\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\GraphQL\DTO\Input\loginInputDTO;
use App\GraphQL\DTO\Input\refreshInputDTO;
use App\GraphQL\DTO\output\loginResponseDTO;
use App\GraphQL\DTO\output\refreshResponseDTO;
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

    #[GQL\Field(type: "loginResponseDTO")]
    #[GQL\Arg(name: "loginInfo", type: "loginInputDTO")]
    public function login(loginInputDTO $loginInfo): ?loginResponseDTO
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $loginInfo->email]);
        if (!$user instanceof UserInterface) {
            throw new AuthenticationException("User {$loginInfo->email} not found");
        }

        if (!$this->passwordHasher->isPasswordValid($user, $loginInfo->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $token = $this->JWTManager->create($user);

        $response = new loginResponseDTO();
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);
        if (!$refreshToken instanceof RefreshToken) { // refresh token is not exists
            $refreshToken = $this->generateRefreshToken($user);
        }
        $response->refresh_token    = $refreshToken->getRefreshToken();
        $response->user             = $user;
        $response->token            = $token;


        $this->logger->info("Provided new JWT token for user '{$user->getUserIdentifier()}'");

        return $response;
    }

    #[GQL\Field(type: "refreshResponseDTO")]
    #[GQL\Arg(name: "refreshInfo", type: "refreshInputDTO")]
    public function refresh(refreshInputDTO $refreshInfo): ?refreshResponseDTO
    {

        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $refreshInfo->refresh_token]);

        if ($refreshToken && $refreshToken->isValid()) {

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $refreshToken->getUsername()]);
            if (!$user instanceof UserInterface) {
                throw new AuthenticationException("User {$refreshToken->getUsername()} not found");
            }

            $token = $this->JWTManager->create($user);
            $newRefreshToken = $this->generateRefreshToken($user, $refreshToken);

        } else {
            throw new InvalidTokenException('Invalid refresh token');
        }

        $response = new refreshResponseDTO();
        $response->token            = $token;
        $response->refresh_token    = $newRefreshToken->getRefreshToken();

        return $response;
    }

    private function generateRefreshToken(User $user, RefreshToken $refreshToken = null): RefreshTokenInterface
    {

        $refresh_token_ttl = $this->configuration->get('gesdinet_jwt_refresh_token.ttl');

        if ($refreshToken instanceof RefreshToken) {
            $this->entityManager->remove($refreshToken); // remove old refresh_token
        }

        $refresh_token = $this->refreshTokenGenerator->createForUserWithTtl($user, $refresh_token_ttl);
        $this->entityManager->persist($refresh_token);
        $this->entityManager->flush();
        return $refresh_token;
    }
}