<?php

namespace App\Controller;

use App\Repository\ProductRepository; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    private ProductRepository $productRepository; 

    public function __construct(ProductRepository $productRepository) 
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        $featuredProducts = $this->productRepository->findFeaturedProducts(); 

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'featuredProducts' => $featuredProducts, 
        ]);
    }
}
