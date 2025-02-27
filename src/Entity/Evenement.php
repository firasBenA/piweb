<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'le contenue doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le contenue ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $contenue = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(
        choices: ['conference', 'seminaire', 'workshop'],
        message: 'Choisissez un type valide'
    )]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'le lieu doit contenir au moins {{ limit }} caractères',
        maxMessage: 'le lieu ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $lieux_event = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\GreaterThanOrEqual(
        'today',
        message: 'La date doit être ultérieure ou égale à aujourd\'hui'
    )]
    private ?\DateTimeInterface $date_event = null;

  

    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: "evenements")]
    #[ORM\JoinTable(name: "evenement_article")]
    private Collection $article;

    public function __construct()
    {
        $this->article = new ArrayCollection();
        $this->date_event = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getLieuxEvent(): ?string
    {
        return $this->lieux_event;
    }

    public function setLieuxEvent(string $lieux_event): static
    {
        $this->lieux_event = $lieux_event;

        return $this;
    }

    public function getDateEvent(): ?\DateTimeInterface
    {
        return $this->date_event;
    }

    public function setDateEvent(\DateTimeInterface $date_event): static
    {
        $this->date_event = $date_event;

        return $this;
    }

  

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->article;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->article->contains($article)) {
            $this->article[] = $article;
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        $this->article->removeElement($article);

        return $this;
    }
}
