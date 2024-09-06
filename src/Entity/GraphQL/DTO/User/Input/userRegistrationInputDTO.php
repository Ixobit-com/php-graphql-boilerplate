<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\User\Input;

use App\Entity\GraphQL\DTO\Common\BaseDTO;
use App\Entity\GraphQL\Role\ExtendedRole;
use App\Entity\User;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['login'],
    message: 'user.login.already.exists',
    entityClass: User::class, errorPath: 'login'
)]
#[GQL\Input(name: 'userRegistrationInputDTO')]
class userRegistrationInputDTO extends BaseDTO
{
    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'user.login.required', allowNull: true)]
    #[Assert\Type(['alnum'], message: 'user.login.invalid.alphanumerical')]
    #[Assert\Length(
        min: User::LOGIN_MIN_LENGTH,
        max: User::LOGIN_MAX_LENGTH,
        minMessage: 'user.login.invalid.min.length',
        maxMessage: 'user.login.invalid.max.length'
    )]
    public string $login;

    #[GQL\InputField(type: 'String')]
    #[Assert\NotBlank(message: 'user.password.required', allowNull: false)]
    public string $password;

    #[GQL\InputField(type: 'profileCreateInputDTO')]
    #[Assert\Valid]
    public profileCreateInputDTO $profile;

    /**
     * Get roles list for validation.
     */
    public function getRoles(): array
    {
        $reflection = new \ReflectionClass(ExtendedRole::class); // All roles except ROLE_SUPERADMIN allowed for registration

        return array_keys($reflection->getConstants());
    }
}
