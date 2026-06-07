<?php

namespace App\Entity;

use App\Repository\RingGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: RingGroupRepository::class)]
#[ORM\Table(name: 'ring_groups')]
#[ORM\Index(columns: ['type_code'], name: 'idx_type_code')]
#[ORM\Index(columns: ['brand'], name: 'idx_brand')]
#[ORM\HasLifecycleCallbacks]
class RingGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $typeCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameRu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nameEn = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $materialEn = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $materialRu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dimensionsPhotoUrl = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $columnNames = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isHidden = false;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'ringGroup', targetEntity: Ring::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Ignore]
    private Collection $rings;

    public function __construct()
    {
        $this->rings = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCode(): ?string
    {
        return $this->typeCode;
    }

    public function setTypeCode(string $typeCode): static
    {
        $this->typeCode = $typeCode;
        return $this;
    }

    public function getNameRu(): ?string
    {
        return $this->nameRu;
    }

    public function setNameRu(?string $nameRu): static
    {
        $this->nameRu = $nameRu;
        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(?string $nameEn): static
    {
        $this->nameEn = $nameEn;
        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    public function getMaterialEn(): ?string
    {
        return $this->materialEn;
    }

    public function setMaterialEn(?string $materialEn): static
    {
        $this->materialEn = $materialEn;
        return $this;
    }

    public function getMaterialRu(): ?string
    {
        return $this->materialRu;
    }

    public function setMaterialRu(?string $materialRu): static
    {
        $this->materialRu = $materialRu;
        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    public function setPhotoUrl(?string $photoUrl): static
    {
        $this->photoUrl = $photoUrl;
        return $this;
    }

    public function getDimensionsPhotoUrl(): ?string
    {
        return $this->dimensionsPhotoUrl;
    }

    public function setDimensionsPhotoUrl(?string $dimensionsPhotoUrl): static
    {
        $this->dimensionsPhotoUrl = $dimensionsPhotoUrl;
        return $this;
    }

    public function getColumnNames(): ?array
    {
        return $this->columnNames;
    }

    public function setColumnNames(?array $columnNames): static
    {
        $this->columnNames = $columnNames;
        return $this;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    public function setIsHidden(bool $isHidden): static
    {
        $this->isHidden = $isHidden;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getRings(): Collection
    {
        return $this->rings;
    }

    public function addRing(Ring $ring): static
    {
        if (!$this->rings->contains($ring)) {
            $this->rings->add($ring);
            $ring->setRingGroup($this);
        }
        return $this;
    }

    public function removeRing(Ring $ring): static
    {
        if ($this->rings->removeElement($ring)) {
            if ($ring->getRingGroup() === $this) {
                $ring->setRingGroup(null);
            }
        }
        return $this;
    }
}
