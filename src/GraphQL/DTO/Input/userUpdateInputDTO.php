<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use Symfony\Component\Validator\Constraints as Assert;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: "userUpdateInputDTO")]
class userUpdateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: "String")]
    #[Assert\Email]
    public ?string $email;

    #[GQL\InputField(type: "String")]
    public ?string $password;

    #[GQL\InputField(type: "profileUpdateInputDTO")]
    public ?profileUpdateInputDTO $profile;

}