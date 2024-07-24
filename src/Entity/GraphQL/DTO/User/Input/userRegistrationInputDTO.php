<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\BaseDTO;
use App\Entity\GraphQL\Role\ExtendedRole;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'userRegistrationInputDTO')]
class userRegistrationInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank]
    public string $login;

    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank]
    public string $password;

    #[GQL\InputField(type: '[String]')]
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Choice(callback: 'getRoles'),
    ])]
    public array $roles;

    #[GQL\InputField(type: 'profileCreateInputDTO')]
    #[Assert\NotBlank]
    public profileCreateInputDTO $profile;

    /**
     * Get roles list for validation.
     */
    public function getRoles(): array
    {
        $reflection = new \ReflectionClass(ExtendedRole::class); // All roles except ROLE_SUPERADMIN allowed for registration

        return array_keys($reflection->getConstants());
    }
}
