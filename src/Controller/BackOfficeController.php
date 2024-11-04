<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Stock;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BackOfficeController extends AbstractController
{   
        /**
     * Affiche le tableau de bord pour les administrateurs.
     *
     * @param ProductRepository $productRepository Le dépôt des produits.
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager L'interface pour gérer les entités.
     * @return Response La réponse HTTP avec la vue du tableau de bord.
     */
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

    /**
     * Gère l'upload d'une image.
     *
     * @param Request $request La requête HTTP contenant le fichier.
     * @return Response La réponse JSON indiquant le succès ou l'échec de l'upload.
     */
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
    
        // Ajouter les tailles par défaut avec des quantités initiales à 0
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        foreach ($sizes as $size) {
            $stock = new Stock();
            $stock->setSize($size); // Définir la taille
            $stock->setQuantity(0); // Quantité initiale à 0
            $product->addStock($stock); // Ajouter le stock au produit
        }
    
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image ici...
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
    
            // Enregistrer le produit et ses stocks
            $entityManager->persist($product); // Persist le produit
            $entityManager->flush(); // Sauvegarde des entités
    
            return $this->redirectToRoute('back_office');
        }
    
        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/product/{id}/edit', name: 'product_edit')]
    public function edit(Request $request, Product $product, EntityManagerInterface $em): Response
    {
        // Créer le formulaire avec le produit existant
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image ici...
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
    
            // Mettre à jour les quantités de stock
            foreach ($product->getStocks() as $stock) {
                // Vérifier si la taille est définie pour le stock
                if ($stock->getSize()) {
                    // Mettre à jour la quantité basée sur le formulaire
                    // Vous devez avoir ces champs dans le formulaire pour que ça fonctionne correctement
                    $quantity = $form->get('stocks')->getData();
                    foreach ($quantity as $updatedStock) {
                        if ($updatedStock->getSize() === $stock->getSize()) {
                            $stock->setQuantity($updatedStock->getQuantity());
                        }
                    }
                }
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
