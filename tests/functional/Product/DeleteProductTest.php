<?php
namespace App\Tests\Functional\Product;

use App\Factory\UserFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductVariantFactory;
use App\Tests\Functional\WebTestCase;
use App\Repository\ProductRepository;
use Zenstruck\Foundry\Test\ResetDatabase;

class DeleteProductTest extends WebTestCase
{
    use ResetDatabase;

    public function testAdminCanDeleteAProduct(): void
    {
        //Authentification admin
        $client = static::createClient();
        $admin  = UserFactory::createOne(['roles' => ['ROLE_ADMIN']]);

        $crawler = $client->request('GET', '/login');
        $client->submit(
            $crawler->selectButton('Se connecter')->form([
                'email'    => $admin->getEmail(),
                'password' => 'Password1!',
            ])
        );
        $client->followRedirect();

        // Création du produit et de ses variantes à mettre à jour
        $product = ProductFactory::createOne([
            'name' => 'OldName-'.uniqid(),
            'price'=> 9.99,
            'isFeatured'=> false
        ]);

        $initialStocks = ['XS'=>2,'S'=>2,'M'=>2,'L'=>2,'XL'=>2];
        foreach ($initialStocks as $size => $stock) {
            ProductVariantFactory::createOne([
                'relation' => $product,
                'size'     => $size,
                'stock'    => $stock,
            ]);
        }
        
        // Suppression
        $crawler = $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Supprimer')->form();

        $client->submit($form);

        //Vérifie la redirection
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // Vérification
        $repo = self::getContainer()->get(ProductRepository::class);
        $deleted = $repo->find($product->getId());

        self::assertNull($deleted, 'Le produit doit être supprimé de la base.');
    }
}