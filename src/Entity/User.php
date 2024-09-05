<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_LOGIN', fields: ['login'])]
#[GQL\Type]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const LOGIN_MIN_LENGTH = 4;
    public const LOGIN_MAX_LENGTH = 180;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[GQL\Field]
    private ?int $id = null;

    #[ORM\Column(length: self::LOGIN_MAX_LENGTH)]
    #[GQL\Field]
    private ?string $login = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[GQL\Field(name: 'roles', type: '[Role]!')]
    #[GQL\Description("User's roles")]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[GQL\Field(type: 'Profile')]
    private ?Profile $profile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }
}
