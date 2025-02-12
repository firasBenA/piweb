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

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    private ?Patient $patient = null;

    #[ORM\ManyToOne(inversedBy: 'diagnostiques')]
    private ?Medecin $medecin = null;

    /**
     * @var Collection<int, Symptomes>
     */
    #[ORM\ManyToMany(targetEntity: Symptomes::class, inversedBy: 'diagnostiques')]
    private Collection $symptomes;

    public function __construct()
    {
        $this->symptomes = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Symptomes>
     */
    public function getSymptomes(): Collection
    {
        return $this->symptomes;
    }

    public function addSymptome(Symptomes $symptome): static
    {
        if (!$this->symptomes->contains($symptome)) {
            $this->symptomes->add($symptome);
        }

        return $this;
    }

    public function removeSymptome(Symptomes $symptome): static
    {
        $this->symptomes->removeElement($symptome);

        return $this;
    }
}
