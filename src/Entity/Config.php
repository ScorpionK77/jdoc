<?php
namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'config')]
#[ORM\UniqueConstraint(name: 'config_vcname_ukey', columns: ['vcname', 'iparentid'])]
#[OA\Schema(
    description: "Конфигурация системы"
)]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\SequenceGenerator(sequenceName: 'config_seq', allocationSize: 1, initialValue: 1)]
    #[ORM\Column(name: 'iconfigid', type: Types::INTEGER)]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Уникальный идентификатор", example: 1)]
    private ?int $iconfigid = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'iparentid', referencedColumnName: 'iconfigid', onDelete: 'CASCADE')]
    #[OA\Property(description: "Родитель", example: 1)]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class, cascade: ['remove'])]
    private Collection $children;

    #[ORM\Column(name: 'vcname', type: Types::STRING, length: 50, options: ['default' => ''])]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Имя")]
    private string $vcname = '';

    #[ORM\Column(name: 'vctitle', type: Types::TEXT)]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Наименование")]
    private string $vctitle;

    #[ORM\Column(name: 'vcvalue', type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Значение")]
    private mixed $vcvalue = null;

    #[ORM\Column(name: 'vctype', type: Types::STRING, length: 50, options: ['default' => 'text'])]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Тип переменной")]
    private string $vctype = 'text';

    /**
     * @var mixed[]|null
     */
    #[ORM\Column(name: 'vcoptions', type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    #[OA\Property(description: "Параметры переменной")]
    private ?array $vcoptions = null;

    #[ORM\Column(name: 'bvisible', type: Types::BOOLEAN, options: ['default' => true])]
    #[OA\Property(description: "Видимость")]
    private bool $bvisible = true;

    #[ORM\Column(name: 'nweight', type: Types::SMALLINT)]
    #[Groups(['config.normal','config.wide'])]
    #[OA\Property(description: "Вес сортировки")]
    private int $nweight;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function getIconfigid(): ?int
    {
        return $this->iconfigid;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    #[Groups(['config.normal','config.wide'])]
    public function getIparentid(): ?int
    {
        return $this->parent?->getIconfigid();
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child))
        {
            $this->children->add($child);
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child))
        {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }

    public function getVcname(): string
    {
        return $this->vcname;
    }

    public function setVcname(string $vcname): static
    {
        $this->vcname = $vcname;
        return $this;
    }

    public function getVctitle(): string
    {
        return $this->vctitle;
    }

    public function setVctitle(string $vctitle): static
    {
        $this->vctitle = $vctitle;
        return $this;
    }

    public function getVcvalue(): mixed
    {
        return $this->vcvalue;
    }

    public function setVcvalue(mixed $vcvalue): static
    {
        $this->vcvalue = $vcvalue;
        return $this;
    }

    public function getVctype(): string
    {
        return $this->vctype;
    }

    public function setVctype(string $vctype): static
    {
        $this->vctype = $vctype;
        return $this;
    }

    /**
     * @return mixed[]|null
     */
    public function getVcoptions(): ?array
    {
        return $this->vcoptions;
    }

    /**
     * @param mixed[]|null $vcoptions
     * @return $this
     */
    public function setVcoptions(?array $vcoptions): static
    {
        $this->vcoptions = $vcoptions;
        return $this;
    }

    public function isBvisible(): bool
    {
        return $this->bvisible;
    }

    public function setBvisible(bool $bvisible): static
    {
        $this->bvisible = $bvisible;
        return $this;
    }

    public function getNweight(): int
    {
        return $this->nweight;
    }

    public function setNweight(int $nweight): static
    {
        $this->nweight = $nweight;
        return $this;
    }

    public function isGroup(): bool
    {
        return $this->vctype == 'group';
    }

    /**
     * @return mixed[]
     */
    public function getConstraints(): array
    {
        return $this->vcoptions['constraints'] ?? [];
    }
}
