<?php
namespace App\Tests\Functional\User;

use App\Factory\UserFactory;
use App\Tests\Functional\WebTestCase;

class RegistrationTest extends WebTestCase
{
    public function testUserCanRegisterAndReceivesConfirmationEmail(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer un compte')->form([
            'registration_form[email]' => 'alice@example.test',
            'registration_form[plainPassword]' => 'S3cret!',
            'registration_form[confirmPassword]' => 'S3cret!',
            'registration_form[name]' => 'Alice',
            'registration_form[delivery_address]' => '10 rue des Tests, 75000 Paris',
            'registration_form[agreeTerms]' => 1, 
        ]);
        $client->submit($form);

        self::assertEmailCount(1);
        $email = self::getMailerMessage(0);
        self::assertStringContainsString('STUBBORN/Confirmez votre e-mail', $email->getSubject());

        self::assertResponseRedirects();
        $client->followRedirect();

        $user = UserFactory::repository()->findOneBy(['email' => 'alice@example.test']);
        self::assertNotNull($user);
        self::assertFalse($user->isVerified());
    }
}
