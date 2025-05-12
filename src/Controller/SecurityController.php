<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Authentification',
    description: 'Connexion et déconnexion des utilisateurs'
)]
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    #[OA\Get(
        path: '/login',
        operationId: 'loginForm',
        summary: 'Afficher le formulaire de connexion',
        tags: ['Authentification'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Page HTML contenant le formulaire de connexion'
            )
        ]
    )]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    #[OA\Get(
        path: '/logout',
        operationId: 'logout',
        summary: 'Déconnexion utilisateur',
        tags: ['Authentification'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Déconnexion réussie – aucune donnée renvoyée'
            )
        ]
    )]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
