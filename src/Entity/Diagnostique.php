<?php

namespace App\Entity;

use App\Repository\DiagnostiqueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DiagnostiqueRepository::class)]
class Diagnostique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDiagnostique = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: DossierMedical::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Please select a body zone.')]
    private ?string $zoneCorps = null;

    #[Assert\Type(type: \DateTimeInterface::class, message: "The date must be a valid date.")]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateSymptomes = null;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'diagnostiques')]
    private ?User $patient = null;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'diagnostiques')]
    private ?User $medecin = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Please select at least one symptom.')]
    #[Assert\Length(min: 1, minMessage: 'Please select at least one symptom.')]
    private ?string $selectedSymptoms = null;



    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function __construct() {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDiagnostique(): ?\DateTimeInterface
    {
        return $this->dateDiagnostique;
    }

    public function setDateDiagnostique(\DateTimeInterface $dateDiagnostique): static
    {
        $this->dateDiagnostique = $dateDiagnostique;

        return $this;
    }

    public function getDateSymptomes(): ?\DateTimeInterface
    {
        return $this->dateSymptomes;
    }

    public function setDateSymptomes(\DateTimeInterface $dateSymptomes): static
    {
        $this->dateSymptomes = $dateSymptomes;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setZoneCorps(string $zoneCorps): static
    {
        $this->zoneCorps = $zoneCorps;

        return $this;
    }

    public function getZoneCorps(): ?string
    {
        return $this->zoneCorps;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDossierMedical(): ?DossierMedical
    {
        return $this->dossierMedical;
    }

    public function setDossierMedical(?DossierMedical $dossierMedical): static
    {
        $this->dossierMedical = $dossierMedical;

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

    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    public function setMedecin(?Medecin $medecin): static
    {
        $this->medecin = $medecin;

        return $this;
    }

    public function getSelectedSymptoms(): String
    {
        return $this->selectedSymptoms;
    }

    public function setSelectedSymptoms(String $selectedSymptoms): self
    {
        $this->selectedSymptoms = $selectedSymptoms;
        return $this;
    }
}
