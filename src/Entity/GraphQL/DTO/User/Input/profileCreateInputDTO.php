<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use App\Entity\Profile;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'profileCreateInputDTO')]
class profileCreateInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'profile.first.name.required')]
    #[Assert\Type(['alnum'], message: 'profile.first.name.invalid.alphanumerical')]
    #[Assert\Length(
        min: Profile::FIRST_NAME_MIN_LENGTH,
        max: Profile::FIRST_NAME_MAX_LENGTH,
        minMessage: 'profile.first.name.min.length',
        maxMessage: 'profile.first.name.max.length'
    )]
    public string $first_name;

    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'profile.last.name.required')]
    #[Assert\Type(['alnum'], message: 'profile.last.name.invalid.alphanumerical')]
    #[Assert\Length(
        min: Profile::LAST_NAME_MIN_LENGTH,
        max: Profile::LAST_NAME_MAX_LENGTH,
        minMessage: 'profile.last.name.min.length',
        maxMessage: 'profile.last.name.max.length'
    )]
    public string $last_name;

    #[GQL\InputField(type: 'String')]
    #[Assert\Email(message: 'profile.email.invalid')]
    #[Assert\NotBlank(message: 'profile.email.required')]
    #[Assert\Length(
        max: Profile::EMAIL_LENGTH,
        maxMessage: 'profile.email.invalid.max.length'
    )]
    public string $email;
}
