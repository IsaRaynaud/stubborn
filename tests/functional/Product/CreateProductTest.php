<?php
namespace App\Tests\Functional\Product;

use App\Factory\UserFactory;
use App\Factory\ProductFactory;
use App\Tests\Functional\WebTestCase;
use App\Repository\ProductRepository;
use Zenstruck\Foundry\Test\ResetDatabase;

class CreateProductTest extends WebTestCase
{
    use ResetDatabase;

    public function testAdminCanCreateAProduct(): void
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

        // Création
        $crawler = $client->request('GET', '/admin');
        self::assertResponseIsSuccessful();

        $productName = 'TestProduct–'.uniqid();
        $form = $crawler->filter('form[name="product"]')->form();
        $form['product[name]']    = $productName;
        $form['product[price]']   = '19.99';
        $form['product[isFeatured]'] = 1;
        $form['product[stockXS]'] = 2;
        $form['product[stockS]']  = 5;
        $form['product[stockM]']  = 3;
        $form['product[stockL]']  = 7;
        $form['product[stockXL]'] = 1;

        $client->submit($form);

        //Vérifie la redirection
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // Confirme la présence du produit en base
        /** @var ProductRepository $repo */
        $repo   = self::getContainer()->get(ProductRepository::class);
        $product = $repo->findOneBy(['name' => $productName]);

        self::assertNotNull($product, "Le produit existe dans la base");
        self::assertTrue($product->isFeatured());
        self::assertSame(1999, $product->getPrice());

        $variants = $product->getVariants();
        $expectedStocks = [
            'XS' => 2,
            'S'  => 5,
            'M'  => 3,
            'L'  => 7,
            'XL' => 1,
        ];
        
        foreach ($expectedStocks as $size => $stock) {
            $v = $variants->filter(fn($v) => $v->getSize() === $size)->first();
            self::assertNotNull($v, "La variante %size existe.");
            self::assertSame($stock, $v->getStock(), "Le stock %size devrait être %stock");
        }   
    }
}