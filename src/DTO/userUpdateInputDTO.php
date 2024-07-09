<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class userUpdateInputDTO extends BaseDTO
{
    #[Assert\Email]
    public string $email;

    public string $password;

    public profileUpdateInputDTO $profile;

}