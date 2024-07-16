<?php

namespace App\Service\GraphQL\Auth;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\GraphQL\DTO\Input\authInputDTO;
use App\GraphQL\DTO\Input\refreshInputDTO;
use App\GraphQL\DTO\output\authResponseDTO;
use App\GraphQL\DTO\output\refreshResponseDTO;
use App\Service\CustomSecurity\Actions;
use App\Service\GraphQL\BaseGraphQLService;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Exception\InvalidTokenException;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthQuery')]
class AuthQueryService extends BaseGraphQLService
{

    #[Actions(Actions::AUTH)]
    #[GQL\Field(type: "authResponseDTO")]
    #[GQL\Arg(name: "authInfo", type: "authInputDTO")]
    public function login(authInputDTO $authInfo): ?authResponseDTO
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $authInfo->email]);
        if (!$user instanceof UserInterface) {
            throw new AuthenticationException("User {$authInfo->email} not found");
        }

        if (!$this->passwordHasher->isPasswordValid($user, $authInfo->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $token = $this->JWTManager->create($user);

        $response = new authResponseDTO();
        $refreshToken = $this->manager->getRepository(RefreshToken::class)->findOneBy(['username' => $user->getUserIdentifier()]);
        if (!$refreshToken instanceof RefreshToken) { // refresh token is not exists
            $refreshToken = $this->generateRefreshToken($user);
        }
        $response->refresh_token    = $refreshToken->getRefreshToken();
        $response->user             = $user;
        $response->token            = $token;

        return $response;
    }

    #[Actions(Actions::AUTH)]
    #[GQL\Field(type: "refreshResponseDTO")]
    #[GQL\Arg(name: "refreshInfo", type: "refreshInputDTO")]
    public function refresh(refreshInputDTO $refreshInfo): ?refreshResponseDTO
    {

        $refreshToken = $this->manager->getRepository(RefreshToken::class)->findOneBy(['refreshToken' => $refreshInfo->refresh_token]);

        if ($refreshToken && $refreshToken->isValid()) {

            $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $refreshToken->getUsername()]);
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
        $ttl = 100000; //@TODO get from config

        if ($refreshToken instanceof RefreshToken) {
            $this->manager->remove($refreshToken); // remove old refresh_token
        }

        $refresh_token = $this->refreshTokenGenerator->createForUserWithTtl($user, $ttl);
        $this->manager->persist($refresh_token);
        $this->manager->flush();
        return $refresh_token;
    }
}