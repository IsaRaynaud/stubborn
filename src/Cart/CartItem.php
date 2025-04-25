<?php

use App\Entity\ProductVariant;

class CartItem
{
    public function __construct(
        public ProductVariant $variant,
        public int $quantity = 1,
    ) {}

    public function getSubtotal(): int
    {
        return $this->variant->getRelation()->getPrice() * $this->quantity;
    }
}