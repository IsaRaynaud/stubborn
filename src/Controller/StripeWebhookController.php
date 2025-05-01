<?php
namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class StripeWebhookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private WorkflowInterface      $orderStateMachine,
        private string                 $webhookSecret
    ) {}

    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->headers->get('stripe-signature');
        $endpointSecret = $this->webhookSecret;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
                //$endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            
            $order = $this->em
                ->getRepository(Order::class)
                ->findOneBy(['stripeSessionId' => $session->id]);

            if ($order && $this->orderStateMachine->can($order, 'pay')) {
                $this->orderStateMachine->apply($order, 'pay');
                $this->em->flush();
            }
        }

        return new Response('Webhook received', 200);
    }
}
