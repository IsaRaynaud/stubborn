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
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Paiement',
    description: 'Processus de checkout Stripe'
)]
#[Route('/checkout', name: 'checkout_')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartManager $cart,
        private EntityManagerInterface $em,
        private StripeClient $stripe,
        private WorkflowInterface $orderStateMachine,
    ) {}

    #[Route('', name: 'start', methods: ['GET', 'POST'])]
    #[OA\Post(  // POST qui déclenche réellement la session Stripe
        path: '/checkout',
        operationId: 'checkoutStartPost',
        summary: 'Créer la session Stripe et rediriger',
        tags: ['Paiement'],
        security: [ ['cookieAuth' => []] ],
        responses: [
            new OA\Response(response: 303, description: 'See Other → URL Stripe'),
            new OA\Response(response: 401, description: 'Non authentifié'),
            new OA\Response(response: 400, description: 'Panier vide')
        ]
    )]
    public function start(): Response
    {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if (null === $this->getUser()) {
            $this->addFlash('warning', 'Vous devez vous connecter pour finaliser votre commande.');
            return $this->redirectToRoute('app_login');
        }

        $items = $this->cart->getItems();
        if (!$items) {
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
        $publicBase = 'https://stubborn.loca.lt';


        $session = $this->stripe->checkout->sessions->create([
            'mode'          => 'payment',
            'success_url'   => $publicBase . $this->generateUrl('checkout_success', ['id' => $order->getId()]),
            'cancel_url'    => $publicBase . $this->generateUrl('cart_show'),
            'line_items'    => array_map(fn($ci) => [
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => $ci->getVariant()->getRelation()->getPrice(),
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

        $data = $session->toArray();
        $url  = $data['url'] ?? null;

        if (!is_string($url)) {
            dd('URL Stripe introuvable dans la session', array_keys($data));
        }

        return $this->redirect($url, Response::HTTP_SEE_OTHER);
        
    }

    #[Route('/success/{id}', name: 'success', requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/checkout/success/{id}',
        operationId: 'checkoutSuccess',
        summary: 'Afficher la page de confirmation de commande',
        tags: ['Paiement'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant de la commande',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page succès avec détails commande'),
            new OA\Response(response: 404, description: 'Commande introuvable')
        ]
    )]
    public function success(Order $order): Response
    {
        return $this->render('checkout/success.html.twig', ['order' => $order]);
    }
}
