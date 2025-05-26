<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Inscription',
    description: "Création de compte et vérification d'e-mail"
)]
class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    #[OA\Post(
        path: '/register',
        operationId: 'register',
        summary: 'Inscrire un nouvel utilisateur',
        tags: ['Inscription'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'plainPassword', type: 'string', format: 'password'),
                    new OA\Property(property: 'confirmPassword', type: 'string', format: 'password')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Compte créé, e-mail de vérification envoyé"),
            new OA\Response(response: 400, description: 'Données invalides ou mots de passe non concordants')
        ]
    )]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new \Symfony\Component\Form\FormError('Les mots de passe ne correspondent pas.'));
            } else {

                //Hash du mot de passe
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

                $user->setRoles(['ROLE_USER']);

                $entityManager->persist($user);
                $entityManager->flush();

                //Gestion du mail de confirmation d'inscription
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@lewebpluschouette.fr', 'Contact'))
                        ->to((string) $user->getEmail())
                        ->subject('STUBBORN/Confirmez votre e-mail')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                return $this->redirectToRoute('app_waiting_confirmation');
            }
        }
            return $this->render('registration/register.html.twig', [
                'registrationForm' => $form,
            ]);
    }

    #[Route('/waiting_confirmation', name: 'app_waiting_confirmation')]
    #[OA\Get(
        path: '/waiting_confirmation',
        operationId: 'waitingConfirmation',
        summary: "Confirme que l'e-mail de vérification a été envoyé",
        tags: ['Inscription'],
        responses: [
            new OA\Response(response: 200, description: 'Vue ou JSON de confirmation')
        ]
    )]
    public function waitingConfirmation(): Response
    {
        return $this->render('registration/confirmation_page.html.twig');
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    #[OA\Get(
        path: '/verify/email',
        operationId: 'verifyEmail',
        summary: "Valider le lien de vérification d'e-mail",
        tags: ['Inscription'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'query',
                required: true,
                description: 'Identifiant du nouvel utilisateur',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 302, description: "Redirection vers la page d'accueil après succès"),
            new OA\Response(response: 400, description: 'Lien invalide ou expiré')
        ]
    )]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash('verify_email_error', 'Test de message en français');

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Votre e-mail a été vérifié.');

        $security->login($user, 'main');

        return $this->redirectToRoute('app_home');

    }
}
