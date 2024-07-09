<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class avatarUploadInputDTO extends BaseDTO
{

    #[Assert\NotBlank]
    public string $file_name;


}