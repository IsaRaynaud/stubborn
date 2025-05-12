<?php

namespace App\Controller;

use App\Form\EditUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Utilisateurs (admin)',
    description: 'Liste et modification des comptes User par un administrateur'
)]
class AdminUserController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users')]
    #[OA\Get(
        path: '/admin/users',
        operationId: 'adminListUsers',
        summary: 'Lister tous les utilisateurs',
        tags: ['Utilisateurs (admin)'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Page HTML ou JSON contenant la liste des utilisateurs'
            )
        ]
    )]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/edit', name: 'admin_user_edit')]
    #[OA\Get(
        path: '/admin/user/{id}/edit',
        operationId: 'adminEditUserForm',
        summary: 'Afficher le formulaire d’édition',
        tags: ['Utilisateurs (admin)'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de l’utilisateur',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Formulaire HTML'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable')
        ]
    )]
    #[OA\Post(
        path: '/admin/user/{id}/edit',
        operationId: 'adminUpdateUser',
        summary: 'Mettre à jour les rôles d’un utilisateur',
        tags: ['Utilisateurs (admin)'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de l’utilisateur',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirection après mise à jour'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 404, description: 'Utilisateur introuvable')
        ]
    )]
    public function editUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $form = $this->createForm(EditUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Rôle mis à jour avec succès.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/edit_user.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}