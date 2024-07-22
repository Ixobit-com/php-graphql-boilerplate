<?php

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: "profileCreateInputDTO")]
class profileCreateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $first_name;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $last_name;

    #[GQL\InputField(type: "String")]
    #[Assert\Email]
    #[Assert\NotBlank]
    public string $email;



}