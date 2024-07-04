<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final class paginationInputDTO extends BaseDTO {

    #[Assert\GreaterThanOrEqual(0)]
    public int $limit = 10;

    #[Assert\GreaterThanOrEqual(0)]
    public int $offset = 0;

}