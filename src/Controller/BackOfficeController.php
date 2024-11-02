<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'back_office')] 
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $products = $productRepository->findAll();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($this->getParameter('images_directory'), $newFilename);
                    $product->setImageFilename($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product);
                $entityManager->persist($stock);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('back_office');
        }

        $productForms = [];
        foreach ($products as $productItem) {
            $productForms[$productItem->getId()] = $this->createProductForm($productItem);
        }

        return $this->render('back-end/back_office.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'editForms' => $productForms,
        ]);
    }

    private function createProductForm(Product $product): \Symfony\Component\Form\FormInterface
    {
        return $this->createForm(ProductType::class, $product);
    }


    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $file = $request->files->get('file');

        if ($file) {
            $newFilename = uniqid().'.'.$file->guessExtension();

            try {
                $file->move($this->getParameter('images_directory'), $newFilename);
            } catch (FileException $e) {
                return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
            }

            return new JsonResponse(['success' => true, 'filename' => $newFilename]);
        }

        return new JsonResponse(['success' => false, 'message' => 'No file uploaded']);
    }

    #[Route('/product/new', name: 'product_new')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
    
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        foreach ($sizes as $size) {
            $stock = new Stock();
            // Assurez-vous de définir la quantité correcte pour chaque taille
            switch ($size) {
                case 'XS':
                    $stock->setQuantityXS(0);
                    break;
                case 'S':
                    $stock->setQuantityS(0);
                    break;
                case 'M':
                    $stock->setQuantityM(0);
                    break;
                case 'L':
                    $stock->setQuantityL(0);
                    break;
                case 'XL':
                    $stock->setQuantityXL(0);
                    break;
            }
            $product->addStock($stock);
        }
    
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image ici...
    
            // Lier chaque stock au produit
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product); // Assurez-vous que chaque stock est lié
                $entityManager->persist($stock);
            }
    
            $entityManager->persist($product);
            $entityManager->flush();
    
            return $this->redirectToRoute('back_office');
        }
    
        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/product/{id}/edit', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stocks = $product->getStocks(); // Récupérer les stocks associés

            foreach ($stocks as $stock) {
                // Mettre à jour la quantité en fonction de la taille
                $stock->setQuantityXS($form->get('quantityXS')->getData());
                $stock->setQuantityS($form->get('quantityS')->getData());
                $stock->setQuantityM($form->get('quantityM')->getData());
                $stock->setQuantityL($form->get('quantityL')->getData());
                $stock->setQuantityXL($form->get('quantityXL')->getData());
            }

            $em->flush(); // Enregistrer les modifications
            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute('back_office');
        }

        return $this->render('produit/product_edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    

}
