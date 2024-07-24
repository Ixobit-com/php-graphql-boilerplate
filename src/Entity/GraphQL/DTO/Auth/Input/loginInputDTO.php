<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\Auth\Input;

use App\Entity\GraphQL\DTO\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'loginInputDTO')]
class loginInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank]
    public string $login;

    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank]
    public string $password;
}
