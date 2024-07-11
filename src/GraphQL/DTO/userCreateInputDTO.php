<?php

namespace App\GraphQL\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class userCreateInputDTO extends BaseDTO
{

    #[Assert\Email]
    public string $email;

    #[Assert\NotBlank]
    public string $password;

    #[Assert\NotBlank]
    public array $roles;

    #[Assert\NotBlank]
    public array $profile;

}