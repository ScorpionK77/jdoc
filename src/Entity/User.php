<?php

namespace App\Entity;

use App\Enum\UserState;
use App\EventListener\Entity\EntityChangeListener;
use App\EventListener\Entity\EntityEventProviderInterface;
use App\Message\UserStateMessage;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\EntityListeners([EntityChangeListener::class])]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EntityEventProviderInterface
{
    use EntityEventProviderTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'users_seq')]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    #[OA\Property(description: 'Уникальный идентификатор документа.')]
    private ?int $iuserid = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    #[OA\Property(description: 'Логин')]
    private ?string $vclogin = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    #[Ignore]
    private ?string $vcpassword = null;

    #[ORM\Column(type: Types::TEXT, length: 255)]
    #[OA\Property(description: 'E-mail адрес')]
    private ?string $vcemail = null;

    #[ORM\Column(enumType: UserState::class, nullable: false)]
    #[OA\Property(description: 'Статус')]
    private UserState $istateid = UserState::PENDING_APPROVAL;

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

    public function getVcemail(): ?string
    {
        return $this->vcemail;
    }

    public function setVcemail(?string $vcemail): static
    {
        $this->vcemail = $vcemail;
        return $this;
    }

    public function getIstateid(): UserState
    {
        return $this->istateid;
    }

    public function setIstateid(UserState $istateid): static
    {
        $this->istateid = $istateid;
        return $this;
    }

    public function getVcstate(): string
    {
        return $this->istateid->label();
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
        return (string)($this->iuserid ?? 'noid');
    }

    // события
    public function getPostPersistEvent(EntityChangeListener $listener): void
    {
        // событие разберется что там делать
        $listener->addEvent(new UserStateMessage($this->getIuserid()));
    }

    public function getPostUpdateEvent(EntityChangeListener $listener, array $changeSet): void
    {
        if (isset($changeSet['istateid']))
        {
            // изменился статус, кинем событие, может нужно чего поделать
            $listener->addEvent(new UserStateMessage($this->getIuserid()));
        }
    }
}
