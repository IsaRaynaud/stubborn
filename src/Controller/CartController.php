<?php
namespace App\Controller;

use App\Cart\CartManager;
use App\Entity\ProductVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartManager $cart,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('cart/show.html.twig', [
            'items'  => $this->cart->getItems(),
            'total'  => $this->cart->getTotal(), // centimes
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(ProductVariant $variant, Request $request): Response
    {
        $qty = max(1, (int) $request->request->get('qty', 1));
        $this->cart->add($variant->getId(), $qty);

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    public function remove(ProductVariant $variant): Response
    {
        $this->cart->remove($variant->getId());

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(): Response
    {
        $this->cart->clear();

        return $this->redirectToRoute('cart_show');
    }
}
