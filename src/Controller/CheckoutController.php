<?php
namespace App\Controller;

use App\Cart\CartManager;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/checkout', name: 'checkout_')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartManager            $cart,
        private EntityManagerInterface $em,
        private StripeClient           $stripe,
        private WorkflowInterface      $orderStateMachine,
    ) {}

    #[Route('', name: 'start', methods: ['GET'])]
    public function start(): Response
    {
        if (!$items = $this->cart->getItems()) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_show');
        }

        //Créer Order + OrderItems
        $order = new Order($this->getUser());
        foreach ($items as $cartItem) {
            $orderItem = new OrderItem(
                $cartItem->getVariant(),
                $cartItem->getQuantity(),
                $cartItem->getVariant()->getRelation()->getPrice()
            );
            $order->addItem($orderItem);
        }
        $order->recalculateTotal();
        $this->em->persist($order);
        $this->em->flush();          // on obtient l’ID avant de parler à Stripe

        //Checkout Session
        $session = $this->stripe->checkout->sessions->create([
            'mode'          => 'payment',
            'success_url'   => $this->generateUrl('checkout_success', ['id' => $order->getId()], true),
            'cancel_url'    => $this->generateUrl('cart_show', [], true),
            'line_items'    => array_map(fn($ci) => [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => $ci->getVariant()->getRelation()->getPrice(), // centimes
                    'product_data' => [
                        'name' => $ci->getVariant()->getRelation()->getName().' - '.$ci->getVariant()->getSize(),
                    ],
                ],
                'quantity' => $ci->getQuantity(),
            ], $items),
        ]);

        $order->setStripeSessionId($session->id);
        $this->orderStateMachine->apply($order, 'to_payment'); // cart → awaiting_payment
        $this->em->flush();

        //Vider panier
        $this->cart->clear();
        return $this->redirect($session->url, Response::HTTP_SEE_OTHER);
    }

    #[Route('/success/{id}', name: 'success', requirements: ['id' => '\d+'])]
    public function success(Order $order): Response
    {
        return $this->render('checkout/success.html.twig', ['order' => $order]);
    }
}
