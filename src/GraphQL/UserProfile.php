<?php

namespace App\GraphQL;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Type]
class UserProfile
{
    #[GQL\Field]
    public string $first_name;
    #[GQL\Field]
    public string $last_name;
}