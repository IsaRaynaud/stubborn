<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Form\ProductType;
use App\Repository\ProductRepository;   
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: 'Produits',
    description: 'Consultation du catalogue et gestion côté admin'
)]
class ProductController extends AbstractController
{
    #[Route('/products', name:'product_index')]
    #[OA\Get(
        path: '/products',
        operationId: 'listProducts',
        summary: 'Lister les produits',
        tags: ['Produits'],
        parameters: [
            new OA\Parameter(
                name: 'range',
                description: 'Filtrer par fourchette de prix (optionnel)',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['10-29', '30-35', '36-50']
                )
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page HTML ou JSON de produits')
        ]
    )]
    public function allProducts(Request $request, ProductRepository $repo): Response
    {
        $ranges = [
            '10-29' => [10, 29],
            '30-35' => [30, 35],
            '36-50' => [36, 50],
        ];

        $rangeKey = $request->query->get('range');

        if ($rangeKey && isset($ranges[$rangeKey])) {
            [$min, $max] = $ranges[$rangeKey];

            $products    = $repo->findByPriceRange($min, $max);
        } else {
            $products    = $repo->findAll();
            $rangeKey    = null;
        }
    
        return $this->render('product/index.html.twig', [
            'products'    => $products,
            'activeRange' => $rangeKey,
        ]);
    }

    #[Route('/product/{id}', name: 'product_detail', requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/product/{id}',
        operationId: 'getProduct',
        summary: 'Afficher un produit',
        tags: ['Produits'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du produit',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page détail produit'),
            new OA\Response(response: 404, description: 'Produit non trouvé')
        ]
    )]
    public function show(Product $product): Response
    {
        return $this->render('product/detail.html.twig', [
            'product' => $product,
        ]);
    }

    //Gestion des produits par les administrateurs
    #[Route('/admin', name: 'admin_dashboard')]
    #[OA\Get(
        path: '/admin',
        operationId: 'adminDashboard',
        summary: 'Tableau de bord admin des produits',
        tags: ['Produits'],
        responses: [
            new OA\Response(response: 200, description: 'Vue HTML du dashboard')
        ]
    )]
    #[OA\Post(
        path: '/admin',
        operationId: 'createProduct',
        summary: 'Créer un produit (admin)',
        tags: ['Produits'],
        responses: [
            new OA\Response(response: 302, description: 'Redirection après création'),
            new OA\Response(response: 400, description: 'Données invalides')
        ]
    )]
    public function dashboard(Request $request, EntityManagerInterface $em, CsrfTokenManagerInterface $csrfTokenManager): Response
    {   
        //Afficher tous les produits
        $products = $em->getRepository(Product::class)->findAll();

        //Ajouter un produit
        $newProduct = new Product();
        $newForm = $this ->createForm(ProductType::class, $newProduct, [
            'csrf_token_id' => 'product',
        ]);
        $newForm->handleRequest($request);

        if ($newForm->isSubmitted() && $newForm->isValid()) {

            $imageFile = $newForm->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $newProduct->setImageFilename($newFilename);
            }

            $this->handleStockBySize($newForm, $newProduct, $em);

            $em->persist($newProduct);
            $em->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        //Formulaire de suppression d'un produit
        $deleteForms = [];
        foreach ($products as $product) {
            $deleteForms[$product->getId()] = $this->createFormBuilder()
                ->setAction($this->generateUrl('product_delete', ['id' => $product->getId()]))
                ->setMethod('POST')
                ->getForm()
                ->createView();
        }

        return $this->render('admin_dashboard.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'newForm' => $newForm->createView(),
            'deleteForms' => $deleteForms,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'product_delete', methods: ['POST'])]
    #[OA\Post(
        path: '/admin/delete/{id}',
        operationId: 'deleteProduct',
        summary: 'Supprimer un produit',
        tags: ['Produits'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID du produit à supprimer',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirection après suppression'),
            new OA\Response(response: 404, description: 'Produit introuvable')
        ]
    )]
    public function delete(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder()
        ->setMethod('POST')
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/product/{id}/edit', name: 'product_edit_page', methods: ['GET', 'POST'])]
    #[OA\Get(
        path: '/admin/product/{id}/edit',
        operationId: 'editProductForm',
        summary: "Afficher le formulaire d'édition (admin)",
        tags: ['Produits'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Formulaire HTML')
        ]
    )]
    #[OA\Post(
        path: '/admin/product/{id}/edit',
        operationId: 'updateProduct',
        summary: 'Mettre à jour un produit',
        tags: ['Produits'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true,
                schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 302, description: 'Redirection après mise à jour'),
            new OA\Response(response: 400, description: 'Données invalides')
        ]
    )]
    public function editPage(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product, [
            'csrf_token_id' => 'product',
        ]);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                    );    
                $product->setImageFilename($newFilename);
            }

            $this->handleStockBySize($form, $product, $em);

            $em->flush();

            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    //Gestion des stocks
    private function handleStockBySize($form, Product $product, EntityManagerInterface $em): void
    {
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        foreach ($sizes as $size) {
            $stockField = 'stock' . $size;
            $stockValue = $form->get($stockField)->getData();

            if ($stockValue === null) {
                continue;
            }

            $variant = null;
            foreach ($product->getVariants() as $v) {
                if ($v->getSize() === $size) {
                    $variant = $v;
                    break;
                }
            }

            if (!$variant) {
                $variant = new ProductVariant();
                $variant->setSize($size);
                $variant->setRelation($product);
                $product->addVariant($variant);
                $em->persist($variant);
            }

            if ($variant) {
                $variant->setStock($stockValue);
            }
        }
    }
}
