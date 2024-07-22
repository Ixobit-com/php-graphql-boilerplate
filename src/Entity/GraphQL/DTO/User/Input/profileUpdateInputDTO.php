<?php

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Email;

#[GQL\Input(name: "profileUpdateInputDTO")]
class profileUpdateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: "String")]
    public ?string $first_name;

    #[GQL\InputField(type: "String")]
    public ?string $last_name;

    #[GQL\InputField(type: "String")]
    #[Assert\Email(message: "Provided email '{{ value }}' is not valid email address", mode: Email::VALIDATION_MODE_STRICT)]
    public ?string $email;

}