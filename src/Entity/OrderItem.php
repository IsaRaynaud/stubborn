<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductVariant $variant = null;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $quantity = 1;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $unitPrice = 0;

    #[ORM\Column(options: ['unsigned' => true])]
    private int $subtotal = 0;

    public function __construct(ProductVariant $variant, int $quantity, int $unitPrice)
    {
        $this->variant = $variant;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subtotal = $quantity * $unitPrice;
    }

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getOrder(): ?Order 
    { 
        return $this->order; 
    }
    
    public function setOrder(?Order $order): self 
    { 
        $this->order = $order; 
        return $this; 
    }

    public function getVariant(): ?ProductVariant
    { 
        return $this->variant; 
    }

    public function setVariant(?ProductVariant $variant): self 
    { 
        $this->variant = $variant; 
        return $this; }

    public function getQuantity(): int 
    { 
        return $this->quantity; 
    }

    public function setQuantity(int $quantity): self 
    { 
        $this->quantity = $quantity; return $this; 
    }

    public function getUnitPrice(): int 
    { 
        return $this->unitPrice; 
    }

    public function setUnitPrice(int $unitPrice): self 
    { 
        $this->unitPrice = $unitPrice; return $this; 
    }

    public function getSubtotal(): int 
    { 
        return $this->subtotal; 
    }

    public function setSubtotal(int $subtotal): self 
    { 
        $this->subtotal = $subtotal; return $this; 
    }
}