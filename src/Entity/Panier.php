<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    #[ORM\JoinColumn(nullable: false)]  // Ensure a user is required
    #[Assert\NotNull(message: "Un patient est obligatoire.")]
    private ?Patient $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de création ne peut pas être vide.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $creeLe = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date de mise à jour ne peut pas être vide.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $majLe = null;

    /**
     * @var Collection<int, ArticleBoutique>
     */
    #[ORM\ManyToMany(targetEntity: ArticleBoutique::class, inversedBy: 'paniers')]
    #[Assert\Valid]
    private Collection $articles;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->creeLe = new \DateTime(); // Automatically set creation date
        $this->majLe = new \DateTime(); // Automatically set update date
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Patient
    {
        return $this->user;
    }

    public function setUser(?Patient $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCreeLe(): ?\DateTimeInterface
    {
        return $this->creeLe;
    }

    public function setCreeLe(\DateTimeInterface $creeLe): static
    {
        $this->creeLe = $creeLe;
        return $this;
    }

    public function getMajLe(): ?\DateTimeInterface
    {
        return $this->majLe;
    }

    public function setMajLe(\DateTimeInterface $majLe): static
    {
        $this->majLe = $majLe;
        return $this;
    }

    /**
     * @return Collection<int, ArticleBoutique>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(ArticleBoutique $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $this->setMajLe(new \DateTime()); // Update timestamp
        }

        return $this;
    }

    public function removeArticle(ArticleBoutique $article): static
    {
        if ($this->articles->removeElement($article)) {
            $this->setMajLe(new \DateTime()); // Update timestamp
        }

        return $this;
    }
}