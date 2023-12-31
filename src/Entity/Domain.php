<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: DomainRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['domain:read']],
    denormalizationContext: ['groups' => ['domain:write']]
)]
class Domain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups('transaction:read')]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read', 'domain:read', 'domain:write'])]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'domain', targetEntity: Category::class, orphanRemoval: true)]
    #[Groups(['domain:read', 'domain:write'])]
    private Collection $categories;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
        $this->categories = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setDomain($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getDomain() === $this) {
                $category->setDomain(null);
            }
        }

        return $this;
    }

    #[Groups('domain:read')]
    public function getTransactionsTotal(): int
    {
        $total = 0;
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $total += $category->getTransactionsTotal();
        }

        return $total;
    }


    #[Groups('domain:read')]
    public function getTransactionsMedium(): int
    {
        $medium = 0;
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $medium += $category->getTransactionsMedium() / $this->categories->count();
        }

        return $medium;
    }
}
