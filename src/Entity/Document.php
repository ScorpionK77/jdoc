<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\Types\DocumentState;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'document_seq')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $idocid = null;

    #[ORM\JoinColumn(name: 'iuserid', referencedColumnName: 'iuserid', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Ignore]
    private UserInterface $user;

    #[ORM\Column(type: Types::STRING)]
    private ?string $state = DocumentState::STATUS_DRAFT;

    #[ORM\Column(type: Types::JSON)]
    private ?array $payload = [];

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private $createAt = null;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private $modifyAt = null;

    public function __construct()
    {
        $this->createAt = new \DateTime();
        $this->modifyAt = new \DateTime();
    }

    public function getIdocid(): ?int
    {
        return $this->idocid;
    }

    public function setIdocid(int $idocid): static
    {
        $this->idocid = $idocid;

        return $this;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;
        return $this;
    }

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(?array $payload): static
    {
        $this->payload = $payload;
        return $this;
    }

    public function getCreateAt(): ?\DateTime
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTime $createAt): static
    {
        $this->createAt = $createAt;
        return $this;
    }

    public function getModifyAt(): ?\DateTime
    {
        return $this->modifyAt;
    }

    public function setModifyAt(\DateTime $modifyAt): static
    {
        $this->modifyAt = $modifyAt;
        return $this;
    }
}
