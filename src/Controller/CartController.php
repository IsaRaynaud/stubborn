<?php
namespace App\Controller;

use App\Cart\CartManager;
use App\Entity\ProductVariant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Panier',
    description: "Gestion du panier de l'utilisateur"
)]
#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartManager $cart,
        private EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/cart',
        operationId: 'cartShow',
        summary: 'Afficher le contenu du panier',
        tags: ['Panier'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Page HTML ou JSON listant les articles du panier'
            )
        ]
    )]
    public function show(): Response
    {
        return $this->render('cart/show.html.twig', [
            'items'  => $this->cart->getItems(),
            'total'  => $this->cart->getTotal(),
        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    #[OA\Post(
        path: '/cart/add/{id}',
        operationId: 'cartAdd',
        summary: 'Ajouter un article au panier',
        tags: ['Panier'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la variante de produit',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: false,
            description: 'Quantité (form-data ou JSON). Défaut : 1',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'qty', type: 'integer', minimum: 1, default: 1)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 302, description: 'Redirection vers le panier'),
            new OA\Response(response: 404, description: 'Produit introuvable')
        ]
    )]
    public function add(ProductVariant $variant, Request $request): Response
    {
        $qty = max(1, (int) $request->request->get('qty', 1));
        $this->cart->add($variant->getId(), $qty);

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    #[OA\Post(
        path: '/cart/remove/{id}',
        operationId: 'cartRemove',
        summary: 'Retirer un article du panier',
        tags: ['Panier'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la variante à retirer',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirection après suppression'),
            new OA\Response(response: 404, description: 'Article introuvable')
        ]
    )]
    public function remove(ProductVariant $variant): Response
    {
        $this->cart->remove($variant->getId());

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    #[OA\Post(
        path: '/cart/clear',
        operationId: 'cartClear',
        summary: 'Vider complètement le panier',
        tags: ['Panier'],
        responses: [
            new OA\Response(response: 302, description: 'Redirection vers le panier (vide)')
        ]
    )]
    public function clear(): Response
    {
        $this->cart->clear();

        return $this->redirectToRoute('cart_show');
    }
}
