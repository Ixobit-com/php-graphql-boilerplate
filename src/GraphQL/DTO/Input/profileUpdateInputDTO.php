<?php

namespace App\GraphQL\DTO\Input;

use App\GraphQL\DTO\BaseDTO;

class profileUpdateInputDTO extends BaseDTO
{
    public string $first_name;

    public string $last_name;

}