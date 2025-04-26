<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    public const STATUS_CART             = 'cart';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PAID             = 'paid';
    public const STATUS_SHIPPED          = 'shipped';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /** @var Collection<int, OrderItem> */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
    private Collection $items;

    #[ORM\Column(length: 32)]
    private string $status = self::STATUS_CART;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $total = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;


    public function __construct()
    {
        $this->items     = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    { 
        return $this->id; 
    }

    public function getUser(): ?User 
    { 
        return $this->user; 
    }
    
    public function setUser(?User $user): self 
    { 
        $this->user = $user; return $this; 
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $item->setOrder($this);
            $this->items->add($item);
            $this->recalculateTotal();
        }

        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            $item->setOrder(null);
            $this->recalculateTotal();
        }

        return $this;
    }

    /**
     * Calcule le total en centimes
     */
    public function recalculateTotal(): self
    {
        $this->total = array_reduce(
            $this->items->toArray(),
            fn(int $carry, OrderItem $item) => $carry + $item->getSubtotal(),
            0
        );

        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(string $sessionId): self
    {
        $this->stripeSessionId = $sessionId;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
