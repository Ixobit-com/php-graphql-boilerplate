<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Input(name: 'userUpdateInputDTO')]
class userUpdateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    public ?string $login;

    #[GQL\InputField(type: 'String')]
    public ?string $password;

    #[GQL\InputField(type: 'profileUpdateInputDTO')]
    public ?profileUpdateInputDTO $profile;
}
