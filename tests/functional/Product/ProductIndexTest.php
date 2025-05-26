<?php
namespace App\Tests\Functional\Product;

use App\Factory\ProductFactory;
use App\Factory\UserFactory;
use App\Tests\Functional\WebTestCase;

class ProductIndexTest extends WebTestCase
{
    public function testProductIndexIsDisplayed(): void
    {
        $client = static::createClient();

        // Création compte admin
        $admin = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Se connecter')->form([
            'email'    => $admin->getEmail(),
            'password' => 'Password1!',
        ]));
        $client->followRedirect(); 

        $product = ProductFactory::createOne();

        $crawler = $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();

        self::assertStringContainsString(
            $product->getName(),
            $client->getResponse()->getContent(),
            "Le nom du produit doit apparaître sur la page d'index."
        );
    }
}