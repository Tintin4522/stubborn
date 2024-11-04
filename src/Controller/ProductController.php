<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\Panier;
use App\Entity\PanierItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Service\CartService;

class ProductController extends AbstractController
{
    

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
