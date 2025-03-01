<?php

namespace App\Entity;

use App\Repository\DossierMedicalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: DossierMedicalRepository::class)]
class DossierMedical
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "The date of prescription is required.")]
    #[Assert\Type("\DateTimeInterface", message: "Please provide a valid date.")]
    private ?\DateTimeInterface $datePrescription = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'dossierMedical')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, Prescription>
     */
    #[ORM\OneToMany(targetEntity: Prescription::class, mappedBy: 'dossierMedical')]
    private Collection $prescriptions;

    /**
     * @var Collection<int, Diagnostique>
     */
    #[ORM\OneToMany(targetEntity: Diagnostique::class, mappedBy: 'dossierMedical')]
    private Collection $diagnostiques;

    public function __construct()
    {
        $this->prescriptions = new ArrayCollection();
        $this->diagnostiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatePrescription(): ?\DateTimeInterface
    {
        return $this->datePrescription;
    }

    public function setDatePrescription(\DateTimeInterface $datePrescription): static
    {
        $this->datePrescription = $datePrescription;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    // Setter for user
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    

    public function getPrescriptions(): Collection
    {
        return $this->prescriptions;
    }

    public function addPrescription(Prescription $prescription): static
    {
        if (!$this->prescriptions->contains($prescription)) {
            $this->prescriptions->add($prescription);
            $prescription->setDossierMedical($this);
        }

        return $this;
    }

    public function removePrescription(Prescription $prescription): static
    {
        if ($this->prescriptions->removeElement($prescription)) {
            // Ensure the owning side is cleared only if it's still referencing this DossierMedical
            if ($prescription->getDossierMedical() === $this) {
                $prescription->setDossierMedical($this); // Keep the relation intact
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Diagnostique>
     */
    public function getDiagnostiques(): Collection
    {
        return $this->diagnostiques;
    }

    public function addDiagnostique(Diagnostique $diagnostique): static
    {
        if (!$this->diagnostiques->contains($diagnostique)) {
            $this->diagnostiques->add($diagnostique);
            $diagnostique->setDossierMedical($this);
        }

        return $this;
    }

    public function removeDiagnostique(Diagnostique $diagnostique): static
    {
        if ($this->diagnostiques->removeElement($diagnostique)) {
            // set the owning side to null (unless already changed)
            if ($diagnostique->getDossierMedical() === $this) {
                $diagnostique->setDossierMedical(null);
            }
        }

        return $this;
    }
}
