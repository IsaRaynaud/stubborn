<?php
namespace App\Cart;

use App\Entity\ProductVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Stocke un tableau sérialisé dans la session :
 * [
 *   variantId (int) => quantity (int),
 *   …
 * ]
 */
class CartManager
{
    private const SESSION_KEY = '_cart';

    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em
    ) {
    }

    /** Ajoute ou incrémente un variant dans le panier */
    public function add(int $variantId, int $qty = 1): void
    {
        $cart = $this->getRaw();
        $cart[$variantId] = ($cart[$variantId] ?? 0) + $qty;
        $this->saveRaw($cart);
    }

    /** Retire complètement le variant du panier */
    public function remove(int $variantId): void
    {
        $cart = $this->getRaw();
        unset($cart[$variantId]);
        $this->saveRaw($cart);
    }

    // Vide le panier
    public function clear(): void
    {
        $this->saveRaw([]);
    }

    // Retourne un tableau d’objets CartItem
    public function getItems(): array
    {
        $items = [];
        foreach ($this->getRaw() as $variantId => $qty) {
            /** @var ProductVariant|null $variant */
            $variant = $this->em->find(ProductVariant::class, $variantId);
            if ($variant) {
                $items[] = new CartItem($variant, $qty);
            }
        }
        return $items;
    }

    //Prix total
    public function getTotal(): int
    {
        return array_reduce($this->getItems(), fn(int $carry, CartItem $item) => $carry + $item->getSubtotal(), 0);
    }


    private function getRaw(): array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY, []);
    }

    private function saveRaw(array $cart): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $cart);
    }
}
