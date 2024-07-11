<?php

namespace App\GraphQL;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Type]
class User
{
    #[GQL\Field]
    public int $id;
    #[GQL\Field]
    public string $email;
    #[GQL\Field(name: "roles", type: "[Role]!")]
    public array $roles;
    #[GQL\Field]
    public UserProfile $profile;
}