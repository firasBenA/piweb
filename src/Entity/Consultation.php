<?php

namespace App\Entity;

use App\Repository\ConsultationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsultationRepository::class)]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le rendez-vous est obligatoire.")]
    private ?RendezVous $rendezVous = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être valide.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être un nombre positif.")]
    private ?int $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Le type de consultation est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le type de consultation doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le type de consultation ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $type_consultation = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User', inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: true)]
    private User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(RendezVous $rendezVous): static
    {
        $this->rendezVous = $rendezVous;

        return $this;
    }

  

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getTypeConsultation(): ?string
    {
        return $this->type_consultation;
    }

    public function setTypeConsultation(string $type_consultation): static
    {
        $this->type_consultation = $type_consultation;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
