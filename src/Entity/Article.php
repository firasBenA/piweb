<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    #[Route('/article/{id}/like', name: 'article_like', methods: ['POST'])]
    #[IsGranted('ROLE_PATIENT')]
    public function like(Article $article, EntityManagerInterface $entityManager): JsonResponse
    {
        $article->setNbJaime($article->getNbJaime() + 1);
        $entityManager->persist($article);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'likes' => $article->getNbJaime()]);
    }
    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'],
        mimeTypesMessage: 'Veuillez télécharger une image au format JPEG, PNG, ou GIF.'
    )]
    
    private ?string $image = null;


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

    public function setImage(?string $image): static
{
    $this->image = $image;
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
