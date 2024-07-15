<?php

namespace App\Service\GraphQL\Auth;

use App\Entity\User;
use App\GraphQL\DTO\Input\authInputDTO;
use App\Service\CustomSecurity\Actions;
use App\Service\GraphQL\BaseGraphQLService;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthQuery')]
class AuthQueryService extends BaseGraphQLService
{

    #[Actions(Actions::AUTH)]
    #[GQL\Field(type: "User")]
    #[GQL\Arg(name: "authInfo", type: "authInputDTO")]
    public function login(authInputDTO $authInfo): ?UserInterface
    {
        $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $authInfo->email]);
        if (!$user instanceof UserInterface) {
            throw new AuthenticationException("User {$authInfo->email} not found");
        }

        if (!$this->passwordHasher->isPasswordValid($user, $authInfo->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $this->security->login($user);

        return $this->security->getUser();
    }

    #[Actions(Actions::AUTH)]
    #[GQL\Field(type: "User")]
    public function logout(): ?UserInterface
    {
        $user = $this->security->getUser();
        $this->security->logout(false);
        return $user;
    }

}