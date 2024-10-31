<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
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

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
