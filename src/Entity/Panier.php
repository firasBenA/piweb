<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $CreeLe = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $MajLe = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\ManyToMany(targetEntity: Produit::class, inversedBy: 'paniers')]
    private Collection $produits;
    
    #[ORM\PrePersist]
    public function setCreerLeValue(): void
    {
        $this->CreerLe = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setMajLeValue(): void
    {
        $this->MajLe = new \DateTimeImmutable();
    }

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getCreeLe(): ?\DateTimeImmutable
    {
        return $this->CreeLe;
    }

    public function setCreeLe(\DateTimeImmutable $CreeLe): static
    {
        $this->CreeLe = $CreeLe;

        return $this;
    }

    public function getMajLe(): ?\DateTimeImmutable
    {
        return $this->MajLe;
    }

    public function setMajLe(\DateTimeImmutable $MajLe): static
    {
        $this->MajLe = $MajLe;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        $this->produits->removeElement($produit);

        return $this;
    }
}
