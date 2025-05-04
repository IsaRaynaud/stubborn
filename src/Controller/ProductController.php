<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ProductController extends AbstractController
{
    #[Route('/products', name:'product_index')]
    public function allProducts(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findAll();
        
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'product_detail', requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/detail.html.twig', [
            'product' => $product,
        ]);
    }

    //Gestion des produits par les administrateurs
    #[Route('/admin', name: 'admin_dashboard')]
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
