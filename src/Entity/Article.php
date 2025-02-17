<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'ce champ est obligatoire.')]
    private ?string $titre = '';

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'ce champ est obligatoire.')]
    
    private ?string $contenue = '';

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'L\'image est obligatoire.')]
    #[Assert\Url(message: 'L\'URL de l\'image n\'est pas valide.')]
    private ?string $image = '';

    #[ORM\Column(nullable: false)]
    #[Assert\Positive(message: 'ce champ doit être un nombre positif')]
    private ?int $prix_article = 0;

    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'ce champ est obligatoire.')]
    
    private ?string $commantaire = '';

    #[ORM\Column(nullable: false)]
    #[Assert\Positive(message: 'Le nombre de j\'aime doit être un nombre positif ou zéro.')]
    private ?int $nbJaime = 0;

    #[ORM\ManyToOne(inversedBy: 'article')]
    private ?Evenement $evenement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenue(): ?string
    {
        return $this->contenue;
    }

    public function setContenue(string $contenue): static
    {
        $this->contenue = $contenue;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getPrixArticle(): ?int
    {
        return $this->prix_article;
    }

    public function setPrixArticle(int $prix_article): static
    {
        $this->prix_article = $prix_article;
        return $this;
    }

    public function getCommantaire(): ?string
    {
        return $this->commantaire;
    }

    public function setCommantaire(string $commantaire): static
    {
        $this->commantaire = $commantaire;
        return $this;
    }

    public function getNbJaime(): ?int
    {
        return $this->nbJaime;
    }

    public function setNbJaime(int $nbJaime): static
    {
        $this->nbJaime = $nbJaime;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): static
    {
        $this->evenement = $evenement;
        return $this;
    }
}
