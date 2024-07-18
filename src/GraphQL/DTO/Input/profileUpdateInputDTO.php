<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: "profileUpdateInputDTO")]
class profileUpdateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: "String")]
    public ?string $first_name;

    #[GQL\InputField(type: "String")]
    public ?string $last_name;

}