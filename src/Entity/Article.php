<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    
    #[ORM\Column(length: 255, nullable: false)]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'],
        mimeTypesMessage: 'Veuillez télécharger une image au format JPEG, PNG, ou GIF.'
    )]
    
    private ?string $image = null;
    
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: "article_likes")]
    private Collection $likedByUsers;

    public function __construct()
    {
        $this->likedByUsers = new ArrayCollection();
    }

    public function getLikedByUsers(): Collection
    {
        return $this->likedByUsers;
    }

    public function like(User $user): void
    {
        if (!$this->likedByUsers->contains($user)) {
            $this->likedByUsers->add($user);
        }
    }

    public function unlike(User $user): void
    {
        $this->likedByUsers->removeElement($user);
    }

    public function isLikedByUser(User $user): bool
    {
        return $this->likedByUsers->contains($user);
    }





    

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'evenement_article')]
    private Collection $evenement;


    //private ?Evenement $evenement = null;

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

    

    

    public function getEvenement(): ?Collection
    {
        return $this->evenement;
    }

    public function setEvenement(?Collection $evenements): static
    {
        $this->evenement = $evenements ?? new ArrayCollection();
        return $this;
    }
    
}
