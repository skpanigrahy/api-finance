<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Elasticsearch\Filter\MatchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ApiResource(
    operations: [
        new Put(),
        new Delete(),
        new Post(),
    ],
    denormalizationContext: ['groups' => ['transaction:write']]
)]
#[ApiResource(
    uriTemplate: '/budgets/{budgetId}/transactions',
    operations: [ new GetCollection() ],
    uriVariables: [
        'budgetId' => new Link(toProperty: 'budget', fromClass: Budget::class),
    ],
    normalizationContext: ['groups' => ['transaction:read']]
)]
#[ApiFilter(SearchFilter::class, properties: ['label' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['amount'])]
#[ApiFilter(MatchFilter::class, properties: ['type'])]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups('transaction:read')]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'transaction:write'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'transaction:write'])]
    private ?int $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'transaction:write'])]
    private ?string $date = null;

    #[ORM\Column(length: 255,  enumType: TransactionType::class)]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'transaction:write'])]
    private ?TransactionType $type = null;

    #[ORM\Column]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'transaction:write'])]
    private boolean $isPending = false;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('transaction:write')]
    private ?Budget $budget = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:read', 'transaction:write'])]
    private ?Category $category = null;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }
    
    public function isPending(): boolean
    {
        return $this->isPending;
    }

    public function setIsPending(?boolean $isPending): self
    {
        $this->isPending = $isPending;

        return $this;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
