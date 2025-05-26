<?php
namespace App\Tests\Functional\Product;

use App\Factory\UserFactory;
use App\Factory\ProductFactory;
use App\Factory\ProductVariantFactory;
use App\Tests\Functional\WebTestCase;
use App\Repository\ProductRepository;
use Zenstruck\Foundry\Test\ResetDatabase;

class EditProductTest extends WebTestCase
{
    use ResetDatabase;

    public function testAdminCanUpdateAProduct(): void
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
        
        // Mise à jour
        $crawler = $client->request('GET', '/admin/product/'.$product->getId().'/edit');
        self::assertResponseIsSuccessful();

        $newName = 'NewName-'.uniqid();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['product[name]']    = $newName;
        $form['product[price]']   = '19.99';
        $form['product[isFeatured]'] = 1;
        $newStocks = ['XS'=>3,'S'=>5,'M'=>3,'L'=>7,'XL'=>1];
        foreach ($newStocks as $size => $stock) {
            $form["product[stock{$size}]"] = $stock;
        }

        $client->submit($form);

        //Vérifie la redirection
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        // Confirme la présence du produit en base
        /** @var ProductRepository $repo */
        $repo = self::getContainer()->get(ProductRepository::class);
        $updated = $repo->find($product->getId());

        self::assertSame($newName, $updated->getName());
        self::assertTrue($updated->isFeatured());
        self::assertSame(1999, $updated->getPrice());

        $variants = $updated->getVariants();
        foreach ($newStocks as $size => $stock) {
            $v = $variants->filter(fn($v) => $v->getSize() === $size)->first();
            self::assertNotNull($v, "La variante %size existe.");
            self::assertSame($stock, $v->getStock(), "Le stock %size devrait être %stock");
        }   
    }
}