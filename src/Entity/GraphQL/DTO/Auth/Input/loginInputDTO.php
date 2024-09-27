<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\Auth\Input;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use App\Entity\User;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'loginInputDTO')]
class loginInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'user.login.required')]
    #[Assert\Type(['alnum'], message: 'user.login.invalid.alphanumerical')]
    #[Assert\Length(
        min: User::LOGIN_MIN_LENGTH,
        max: User::LOGIN_MAX_LENGTH,
        minMessage: 'user.login.invalid.min.length',
        maxMessage: 'user.login.invalid.max.length'
    )]
    public string $login;

    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'user.password.required')]
    public string $password;
}
