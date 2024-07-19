<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use App\GraphQL\DTO\Role\BaseRole;
use App\GraphQL\DTO\Role\ExtendedRole;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: "userRegistrationInputDTO")]
class userRegistrationInputDTO extends BaseDTO
{

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $login;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $password;

    #[GQL\InputField(type: "[String]")]
    #[Assert\NotBlank]
    #[Assert\All([
        new Assert\Choice(callback: 'getRoles')
    ])]
    public array $roles;

    #[GQL\InputField(type: "profileCreateInputDTO")]
    #[Assert\NotBlank]
    public profileCreateInputDTO $profile;

    /**
     * Get roles list for validation
     * @return array
     */
    public function getRoles():array
    {
        $reflection = new \ReflectionClass(ExtendedRole::class); // All roles except ROLE_SUPERADMIN allowed for registration
        return array_keys($reflection->getConstants());
    }
}