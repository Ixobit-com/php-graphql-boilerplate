<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class userUpdateInputDTO extends BaseDTO
{
    #[Assert\NotBlank]
    public int $id;

    #[Assert\Email]
    public string $email;

    public string $password;

    public array $roles;

    public array $profile;

}