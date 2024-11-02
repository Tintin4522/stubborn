<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'stocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer')]
    private $quantity_xs = null;

    #[ORM\Column(type: 'integer')]
    private $quantity_s = null;

    #[ORM\Column(type: 'integer')]
    private $quantity_m = null;

    #[ORM\Column(type: 'integer')]
    private $quantity_l = null;

    #[ORM\Column(type: 'integer')]
    private $quantity_xl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantityXS(): ?int
    {
        return $this->quantity_xs;
    }

    public function setQuantityXS(int $quantityXS): self
    {
        $this->quantity_xs = $quantityXS;
        return $this;
    }

    public function getQuantityS(): ?int
    {
        return $this->quantity_s;
    }

    public function setQuantityS(int $quantityS): self
    {
        $this->quantity_s = $quantityS;
        return $this;
    }

    public function getQuantityM(): ?int
    {
        return $this->quantity_m;
    }

    public function setQuantityM(int $quantityM): self
    {
        $this->quantity_m = $quantityM;
        return $this;
    }

    public function getQuantityL(): ?int
    {
        return $this->quantity_l;
    }

    public function setQuantityL(int $quantityL): self
    {
        $this->quantity_l = $quantityL;
        return $this;
    }

    public function getQuantityXL(): ?int
    {
        return $this->quantity_xl;
    }

    public function setQuantityXL(int $quantityXL): self
    {
        $this->quantity_xl = $quantityXL;
        return $this;
    }
}

