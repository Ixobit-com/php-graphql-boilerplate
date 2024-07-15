<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;
use Symfony\Component\Validator\Constraints as Assert;

class userUpdateInputDTO extends BaseDTO
{
    #[Assert\Email]
    public string $email;

    public string $password;

    public profileUpdateInputDTO $profile;

}