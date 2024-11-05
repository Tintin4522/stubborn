<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Panier; 
use App\Entity\PanierItem; 
use App\Entity\User; 
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CartService
{
    private EntityManagerInterface $entityManager;
    private ?Panier $panier; // Propriété nullable pour Panier

    public function __construct(EntityManagerInterface $entityManager, $userId = null)
    {
        $this->entityManager = $entityManager;
        $this->panier = null; // Initialisé à null

        if ($userId !== null) {
            $this->loadPanier($userId); // Chargez le panier si l'ID de l'utilisateur est fourni
        }
    }

    public function loadPanier($userId): void
    {
        // Charger le panier existant
        $this->panier = $this->entityManager->getRepository(Panier::class)->findOneBy(['user' => $userId]);

        // Si aucun panier n'est trouvé, créez-en un nouveau
        if ($this->panier === null) {
            $this->panier = new Panier();
            $user = $this->entityManager->getRepository(User::class)->find($userId); 
            if ($user !== null) {
                $this->panier->setUser($user); 
            } else {
                throw new \Exception("L'utilisateur avec l'ID $userId n'a pas été trouvé.");
            }
            
            // Persister le nouveau panier
            $this->entityManager->persist($this->panier);
            $this->entityManager->flush();
        }
    }

    // Méthode pour obtenir les éléments du panier
    public function getCartItems(): Collection
    {
        if ($this->panier !== null) {
            return $this->panier->getItems(); 
        }

        return new ArrayCollection(); 
    }
    
}
