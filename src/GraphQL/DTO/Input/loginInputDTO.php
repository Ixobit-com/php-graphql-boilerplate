<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: "loginInputDTO")]
class loginInputDTO extends BaseDTO
{

    #[GQL\InputField(type: "String")]
    #[Assert\Email]
    #[Assert\NotBlank]
    public string $email;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $password;

}