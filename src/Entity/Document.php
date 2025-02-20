<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Doctrine\Types\DocumentState;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'document_seq')]
    #[ORM\Column(type: Types::INTEGER)]
    #[OA\Property(description: 'Уникальный идентификатор документа.')]
    private ?int $idocid;

    #[ORM\JoinColumn(name: 'iuserid', referencedColumnName: 'iuserid', nullable: false)]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Ignore]
    private UserInterface $user;

    #[ORM\Column(type: Types::STRING)]
    #[OA\Property(description: 'Статус документа')]
    private ?string $state = DocumentState::STATUS_DRAFT;

    #[ORM\Column(type: Types::JSON)]
    #[OA\Property(description: 'JSON тело документа.')]
    private ?array $payload = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[OA\Property(description: 'Дата создания.')]
    private \DateTimeImmutable $createAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[OA\Property(description: 'Дата последнего изменения.')]
    private \DateTimeImmutable $modifyAt;

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

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;
        return $this;
    }

    public function getModifyAt(): ?\DateTimeImmutable
    {
        return $this->modifyAt;
    }

    public function setModifyAt(\DateTimeImmutable $modifyAt): static
    {
        $this->modifyAt = $modifyAt;
        return $this;
    }

    #[ORM\PrePersist]
    public function initCreateAt()
    {
        $this->createAt = new \DateTimeImmutable();
    }

    #[ORM\PreFlush]
    public function updateLastModify()
    {
        $this->setModifyAt(new \DateTimeImmutable());
    }
}
