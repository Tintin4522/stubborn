<?php
// src/Controller/ShopController.php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'shop_home')]
    public function index(): Response
    {
        return $this->render('shop/index.html.twig');
    }

    #[Route('/products', name: 'shop_products')]
    public function products(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les produits depuis la base de données
        $products = $entityManager->getRepository(Product::class)->findAll();

        // Récupérer la tranche de prix depuis la requête
        $priceRange = $request->query->get('priceRange');

        // Filtrer les produits par tranche de prix
        if ($priceRange) {
            $filteredProducts = [];
            foreach ($products as $product) {
                $price = $product->getPrice();
                if ($this->isPriceInRange($price, $priceRange)) {
                    $filteredProducts[] = $product;
                }
            }
            $products = $filteredProducts;
        }

        // Passer les produits (filtrés ou non) à la vue
        return $this->render('produit/products.html.twig', [
            'Products' => $products,
        ]);
    }

    private function isPriceInRange(float $price, string $priceRange): bool
    {
        switch ($priceRange) {
            case '10-30':
                return $price >= 10 && $price <= 30;
            case '30-35':
                return $price > 30 && $price <= 35;
            case '35-50':
                return $price > 35 && $price <= 50;
            default:
                return true; // Si aucun filtre, retourner true
        }
    }

}

