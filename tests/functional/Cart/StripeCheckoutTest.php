<?php

namespace App\Tests\Functional;

use App\Factory\UserFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductVariantFactory;
use App\Entity\User;
use App\Entity\Order;
use App\Tests\Stub\FakeStripeClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class StripeCheckoutTest extends WebTestCase
{
    use ResetDatabase;

    public function testFullCheckoutFlow(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $container->set(\Stripe\StripeClient::class, new FakeStripeClient());

        // Création du produit et connexion
        $variant = ProductVariantFactory::createOne([
            'size'     => 'M',
            'relation' => ProductFactory::new([
                'name'  => 'T-shirt test',
                'price' => 1999,
            ]),
        ]);

        //Création de l'utilisateur et connexion
        /** @var User $user */
        $user = UserFactory::createOne();
        $em->flush();

        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Se connecter')->form([
            'email'    => $user->getEmail(),
            'password' => 'Password1!',
        ]));
        $client->followRedirect();

        //Création du panier
        /** @var SessionInterface $session */
        $session = $client->getRequest()->getSession();
        $session->set('_cart', [$variant->getId() => 1]);
        $session->save();

        //Redirection vers Stripe
        $client->request('POST', '/checkout');
        $this->assertResponseStatusCodeSame(303);
        $this->assertSame('https://stripe.test/checkout/cs_test_123', $client->getResponse()->headers->get('Location'));

        /** @var User $managedUser */
        $managedUser = $em->find(User::class, $user->getId());

        /** @var Order $order */
        $order = $em->getRepository(Order::class)->findOneBy(['user' => $user->getId()]);
        $this->assertNotNull($order, 'La commande a été créée');
        $this->assertSame(Order::STATUS_AWAITING_PAYMENT, $order->getStatus());
        $this->assertSame('cs_test_123', $order->getStripeSessionId());

        $orderId = $order->getId();

        // Webhook checkout.session.completed
        $payload = json_encode([
            'id'     => 'evt_test_123',
            'object' => 'event',
            'type'   => 'checkout.session.completed',
            'data'   => [
                'object' => [
                    'id'     => 'cs_test_123',
                    'object' => 'checkout.session',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $webhookController = $container->get(\App\Controller\StripeWebhookController::class);
        $ref = new \ReflectionClass($webhookController);
        $prop = $ref->getProperty('webhookSecret');
        $prop->setAccessible(true);
        $secret = $prop->getValue($webhookController);

        $timestamp = time();
        $signedBody = $timestamp . '.' . $payload;
        $signature = hash_hmac('sha256', $signedBody, $secret);
        $header = sprintf('t=%d,v1=%s', $timestamp, $signature);

        $client->request(
            'POST',
            '/stripe/webhook',
            server: ['HTTP_Stripe-Signature' => $header],
            content: $payload
        );
        $this->assertResponseIsSuccessful();

        // Commande "paid"
        $order = $em->find(Order::class, $orderId);
        $this->assertSame(Order::STATUS_PAID, $order->getStatus());
    }
}