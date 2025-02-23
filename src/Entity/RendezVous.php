<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le médecin est obligatoire.")]
    private ?Medecin $medecin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être valide.")]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: "La date ne peut pas être antérieure à aujourd'hui."
    )]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de rendez-vous est obligatoire.")]
    
    private ?string $type_rdv = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La cause est obligatoire.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "La cause doit contenir au moins {{ limit }} caractères.",
        maxMessage: "La cause ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $cause = null;

    #[ORM\OneToOne(mappedBy: 'rendezVous', cascade: ['persist', 'remove'])]
    private ?Consultation $consultation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'rendezVouses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): static
    {
        $this->patient = $patient;
        return $this;
    }

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getTypeRdv(): ?string
    {
        return $this->type_rdv;
    }

    public function setTypeRdv(string $type_rdv): static
    {
        $this->type_rdv = $type_rdv;
        return $this;
    }

    public function getCause(): ?string
    {
        return $this->cause;
    }

    public function setCause(string $cause): static
    {
        $this->cause = $cause;
        return $this;
    }

    public function getConsultation(): ?Consultation
    {
        return $this->consultation;
    }

    public function setConsultation(?Consultation $consultation): static
    {
        $this->consultation = $consultation;
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
