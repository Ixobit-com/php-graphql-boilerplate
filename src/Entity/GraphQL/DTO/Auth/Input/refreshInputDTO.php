<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\Auth\Input;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'refreshInputDTO')]
class refreshInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'user.refresh.token.required')]
    public string $refresh_token;
}
