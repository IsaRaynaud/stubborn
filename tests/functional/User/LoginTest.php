<?php

namespace App\Tests\Functional\User;

use App\Factory\UserFactory;
use App\Tests\Functional\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class LoginTest extends WebTestCase
{
    /**
     * @dataProvider provideRolesAndTargets
     */
    public function testLoginRedirectsToProperPage(array $roles, string $expectedRoute): void
    {
        $client = static::createClient();

        $user = UserFactory::createOne([
            'email' => 'bob@example.test',
            'roles' => $roles,
        ]);
        
        $router = self::getContainer()->get(RouterInterface::class);

        // Connexion
        $crawler = $client->request('GET', '/login');
        $client->submit(
            $crawler->selectButton('Se connecter')->form([
                'email' => $user->getEmail(),
                'password' => 'Password1!',
            ])
        );

        //Redirection en fonction du rôle
        self::assertResponseRedirects($router->generate($expectedRoute));
    }

    public static function provideRolesAndTargets(): iterable
    {
        yield 'simple user' => [['ROLE_USER'], 'app_home'];
        yield 'admin user' => [['ROLE_ADMIN'], 'admin_dashboard'];
    }

    public function testLogoutClearsSession(): void
    {
        $client = static::createClient();
        $user = UserFactory::createOne();

        //Connexion
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Se connecter')->form([
            'email'    => $user->getEmail(),
            'password' => 'Password1!',
        ]));
        $client->followRedirect();

        //Déconnexion
        $client->request('GET', '/logout');
        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertNull($client->getContainer()->get('security.token_storage')->getToken());
    }
}