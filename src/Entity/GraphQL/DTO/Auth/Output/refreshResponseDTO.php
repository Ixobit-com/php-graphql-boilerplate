<?php

namespace App\Entity\GraphQL\DTO\Auth\Output;

use App\Entity\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Type(name: "refreshResponseDTO")]
class refreshResponseDTO extends BaseDTO
{

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $token;

    #[GQL\InputField(type: "String")]
    #[Assert\NotBlank]
    public string $refresh_token;

}