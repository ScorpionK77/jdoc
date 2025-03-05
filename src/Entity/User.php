<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'users_seq')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $iuserid = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    private ?string $vclogin = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    #[Ignore]
    private ?string $vcpassword = null;

    public function getIuserid(): ?int
    {
        return $this->iuserid;
    }

    public function setIuserid(int $iuserid): static
    {
        $this->iuserid = $iuserid;

        return $this;
    }

    public function getVclogin(): ?string
    {
        return $this->vclogin;
    }

    public function setVclogin(string $vclogin): static
    {
        $this->vclogin = $vclogin;

        return $this;
    }

    #[Ignore]
    public function getVcpassword(): ?string
    {
        return $this->vcpassword;
    }

    public function setVcpassword(string $vcpassword): static
    {
        $this->vcpassword = $vcpassword;

        return $this;
    }

    #[Ignore]
    public function getPassword(): ?string
    {
        return $this->vcpassword;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    #[Ignore]
    public function getUserIdentifier(): string
    {
        return (string) $this->iuserid;
    }
}
