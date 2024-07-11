<?php

namespace App\GraphQL\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class profileCreateInputDTO extends BaseDTO
{
    #[Assert\NotBlank]
    public string $first_name;

    #[Assert\NotBlank]
    public string $last_name;

}