<?php

namespace App\Service;

use App\Entity\Stock;
use App\Entity\Product;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;


class StockService {
    private $stockRepository;
    private $entityManager;

    public function __construct(StockRepository $stockRepository, EntityManagerInterface $entityManager) {
        $this->stockRepository = $stockRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Ajoute ou met à jour le stock pour un produit donné et une taille spécifiée.
     *
     * @param Product $product Le produit pour lequel on gère le stock.
     * @param string $size La taille du produit.
     * @param int $quantity La quantité à ajouter ou à mettre à jour.
     * @return void
     */
    public function manageStock(Product $product, string $size, int $quantity): void {
        // Vérifier si un stock existe déjà pour ce produit et cette taille
        $stock = $this->stockRepository->findStockByProductAndSize($product, $size);

        if ($stock) {
            // Mettre à jour la quantité existante
            $stock->setQuantity($stock->getQuantity() + $quantity);
        } else {
            // Créer un nouveau stock
            $stock = new Stock();
            $stock->setProduct($product);
            $stock->setSize($size);
            $stock->setQuantity($quantity);
        }

        // Enregistrer le stock dans la base de données
        $this->entityManager->persist($stock);
        $this->entityManager->flush();
    }
}
