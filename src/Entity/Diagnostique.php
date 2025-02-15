<?php

namespace App\Entity;

use App\Repository\DiagnostiqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DiagnostiqueRepository::class)]
class Diagnostique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateDiagnostique = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]  // Added `nullable: true`
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    #[ORM\JoinColumn(nullable: false)]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    private ?Medecin $medecin = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(type: 'json')]
    private array $symptoms = [];


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

    public function getNom(): ?string
    {
        return $this->nom;
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

    public function getSymptoms(): array
    {
        return $this->symptoms;
    }

    public function setSymptoms(array $symptoms): self
    {
        $this->symptoms = $symptoms;
        return $this;
    }
}
