<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Panier;
use App\Entity\PanierItem;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\CartService;

class ProductController extends AbstractController
{
    #[Route('/back-office', name: 'back_office')]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $products = $productRepository->findAll();
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'), // Vérifiez que ce paramètre est bien défini
                        $newFilename
                    );
                    $product->setImageFilename($newFilename); // Enregistrez le nom de fichier dans l'entité
                } catch (FileException $e) {
                    // Gérer l'exception
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            // Ajoutez les stocks au produit avant de persister
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product); // Assurez-vous que chaque Stock connaît son produit
                $entityManager->persist($stock); // Persistez chaque stock
            }

            // Persist le produit
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('back_office'); // Assurez-vous que cette route existe
        }

        // Créez un tableau pour stocker les formulaires pour chaque produit
        $productForms = [];
        foreach ($products as $productItem) {
            $productForms[$productItem->getId()] = $this->createProductForm($productItem);
        }

        $editForms = [];
        foreach ($products as $product) {
            $editForms[$product->getId()] = $this->createForm(ProductType::class, $product);
        }

        return $this->render('back-end/back_office.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'editForms' => $productForms, // Passez les formulaires au template
        ]);
    }

    // Ajoutez cette méthode à votre ProductController
    private function createProductForm(Product $product): \Symfony\Component\Form\FormInterface
    {
        return $this->createForm(ProductType::class, $product);
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): Response
    {
        $file = $request->files->get('file'); // Le champ 'file' est celui par défaut de Dropzone

        if ($file) {
            $newFilename = uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('images_directory'), // Défini dans config/services.yaml
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'exception si quelque chose ne va pas pendant l'upload
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
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    // Assurez-vous de définir l'image avant de persister le produit
                    $product->setImageFilename($newFilename); 
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            // Ajoutez les stocks au produit avant de persister
            foreach ($product->getStocks() as $stock) {
                $stock->setProduct($product); // Liez chaque Stock au produit
                $entityManager->persist($stock); // Persistez chaque stock
            }

            // Persist le produit et tous les changements effectués
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('back_office'); // Assurez-vous que cette route existe
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/product/{id}', name: 'product_show')]
    public function show(Product $product): Response
    {
        return $this->render('produit/product_show.html.twig', [
            'product' => $product,
        ]);
    }
    
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('/product/{id}/delete', name: 'product_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        // Supprimer l'image associée si elle existe
        if ($product->getImageFilename()) {
            $imagePath = $this->getParameter('images_directory') . '/' . $product->getImageFilename();
            if (file_exists($imagePath)) {
                unlink($imagePath); // Supprime physiquement l'image du serveur
            }
        }

        // Supprimez les stocks associés
        foreach ($product->getStocks() as $stock) {
            $entityManager->remove($stock);
        }

        // Maintenant, supprimez le produit
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('back_office');
    }

    #[Route('/product/{id}/edit', name: 'product_edit')]
    public function editForm(Product $product): Response
    {
        // Créez le formulaire avec le produit
        $form = $this->createForm(ProductType::class, $product);

        return $this->render('produit/product_edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/update', name: 'product_update', methods: ['POST'])]
    public function update(Request $request, EntityManagerInterface $entityManager, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Produit mis à jour avec succès.');

            return $this->redirectToRoute('back_office');
        }

        // Si le formulaire n'est pas valide, vous pouvez rediriger vers la page d'édition ou afficher les erreurs
        return $this->render('product/product_edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/add-to-cart', name: 'add_to_cart')]
    public function addToCart(Product $product, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Vérifiez que l'utilisateur est connecté
        if (!$user || !($user instanceof User)) {
            // Redirigez vers la page de connexion si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app_login');
        }

        // Vérifiez si l'utilisateur a déjà un panier, sinon créez-en un
        $panierRepository = $entityManager->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['user' => $user]);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUser($user);
            $entityManager->persist($panier);
        }

        // Récupérez la taille depuis le formulaire
        $size = $request->request->get('size');

        // Ajoutez le produit en tant qu'élément du panier
        $panierItem = new PanierItem();
        $panierItem->setProduct($product);
        $panierItem->setQuantity(1); // Définissez la quantité, par exemple 1 par défaut
        $panierItem->setPanier($panier);
        $panierItem->setSize($size);
        
        $entityManager->persist($panierItem);
        $entityManager->flush();

        $this->addFlash('success', 'Produit ajouté au panier avec succès.');

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart', name: 'cart_show')]
    public function cart(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Obtenez l'utilisateur connecté

        // Vérifiez que l'utilisateur est connecté et que c'est une instance de User
        if (!$user || !($user instanceof User)) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app_login'); // Modifiez selon le nom de votre route de connexion
        }

        // Chargez le panier avec l'ID de l'utilisateur
        $this->cartService->loadPanier($user->getId());

        // Récupérez les articles du panier
        $cartItems = $this->cartService->getCartItems();

        // Calcul de la somme des prix des articles du panier
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->getPrice(); // Assurez-vous que cette méthode existe dans votre objet d'item
        }

        // Rendre le template avec les articles du panier et le total
        return $this->render('panier/cart.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total, 
        ]);
    }


    #[Route('/cart/remove-item/{id}', name: 'cart_remove_item')]
    public function removeItem(PanierItem $item, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($item);
        $entityManager->flush();

        $this->addFlash('success', 'Produit retiré du panier.');
        return $this->redirectToRoute('cart_show');
    }


}
