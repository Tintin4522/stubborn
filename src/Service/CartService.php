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
            
            // Assurez-vous d'associer le panier à l'utilisateur
            $user = $this->entityManager->getRepository(User::class)->find($userId); // Assurez-vous d'importer la classe User
            if ($user !== null) {
                $this->panier->setUser($user); // Associer l'utilisateur au panier
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
        // Vérifiez que $panier est initialisé avant d'y accéder
        if ($this->panier !== null) {
            return $this->panier->getItems(); // Utilisez la méthode getItems de l'entité Panier
        }

        return new ArrayCollection(); // Retourne une collection vide si aucun panier n'est trouvé
    }

    // Ajoute un article au panier
    public function addItemToCart(PanierItem $item): void
    {
        if ($this->panier !== null) {
            $this->panier->addItem($item); // Assurez-vous que la méthode addItem existe dans votre entité Panier
            $this->entityManager->persist($this->panier);
            $this->entityManager->flush();
        }
    }

    // Retire un article du panier
    public function removeItemFromCart(PanierItem $item): void
    {
        if ($this->panier !== null) {
            $this->panier->removeItem($item); // Assurez-vous que la méthode removeItem existe dans votre entité Panier
            $this->entityManager->persist($this->panier);
            $this->entityManager->flush();
        }
    }
}
