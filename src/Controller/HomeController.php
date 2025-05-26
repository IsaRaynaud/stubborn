<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Accueil',
    description: "Page d'accueil"
)]
class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    #[OA\Get(
        path: '/',
        operationId: 'home',
        summary: "Afficher la page d'accueil",
        tags: ['Accueil'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Page HTML contenant les produits en vedette'
            )
        ]
    )]
    public function index(ProductRepository $repository): Response
    {
        $featuredProducts = $repository->findFeatured();

        return $this->render('home/index.html.twig', [
            'products' => $featuredProducts,
        ]);
    }
}