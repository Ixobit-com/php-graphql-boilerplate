<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use App\GraphQL\DTO\Role;
use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: "userRegistrationInputDTO")]
class userRegistrationInputDTO extends BaseDTO
{

    #[GQL\InputField(type: "String")]
    #[Assert\Email]
    #[Assert\NotBlank]
    public string $email;

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
        $reflection = new \ReflectionClass(Role::class);
        return array_keys($reflection->getConstants());
    }
}