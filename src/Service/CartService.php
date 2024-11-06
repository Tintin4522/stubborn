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
    private ?Panier $panier; 

    public function __construct(EntityManagerInterface $entityManager, $userId = null)
    {
        $this->entityManager = $entityManager;
        $this->panier = null; 

        if ($userId !== null) {
            $this->loadPanier($userId); 
        }
    }

    public function loadPanier($userId): void
    {
        $this->panier = $this->entityManager->getRepository(Panier::class)->findOneBy(['user' => $userId]);

        if ($this->panier === null) {
            $this->panier = new Panier();
            $user = $this->entityManager->getRepository(User::class)->find($userId); 
            if ($user !== null) {
                $this->panier->setUser($user); 
            } else {
                throw new \Exception("L'utilisateur avec l'ID $userId n'a pas été trouvé.");
            }
            
            $this->entityManager->persist($this->panier);
            $this->entityManager->flush();
        }
    }

    public function getCartItems(): Collection
    {
        if ($this->panier !== null) {
            return $this->panier->getItems(); 
        }

        return new ArrayCollection(); 
    }
    
}
