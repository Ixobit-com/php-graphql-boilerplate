<?php

namespace App\Entity\GraphQL\DTO\Auth\Output;

use App\Entity\GraphQL\DTO\BaseDTO;
use App\Entity\User;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Type(name: "loginResponseDTO")]
class loginResponseDTO extends BaseDTO
{

    #[GQL\InputField(type: "User")]
    public User $user;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $token;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $refresh_token;

}