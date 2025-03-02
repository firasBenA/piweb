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

    // Relation avec le rendez-vous
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le rendez-vous est obligatoire.")]
    private ?RendezVous $rendezVous = null;

    // Relation avec User (patient)
    #[ORM\ManyToOne(inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le patient est obligatoire.")]
    private ?User $patient = null;

    // Relation avec User (médecin)
    #[ORM\ManyToOne(inversedBy: 'consultations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le médecin est obligatoire.")]
    private ?User $medecin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être valide.")]
    private ?\DateTimeInterface $date = null;

    

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(message: "Le type de consultation est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Le type de consultation doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le type de consultation ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $type_consultation = null;

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

    public function getPatient(): ?User
    {
        return $this->patient;
    }

    public function setPatient(?User $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getMedecin(): ?User
    {
        return $this->medecin;
    }

    public function setMedecin(?User $medecin): static
    {
        $this->medecin = $medecin;
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

 

    public function getTypeConsultation(): ?string
    {
        return $this->type_consultation;
    }

    public function setTypeConsultation(string $type_consultation): static
    {
        $this->type_consultation = $type_consultation;
        return $this;
    }
}
