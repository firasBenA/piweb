<?php

namespace App\Entity;

use App\Repository\PrescriptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrescriptionRepository::class)]
class Prescription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $contenue = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datePrscription = null;

    #[ORM\ManyToOne(inversedBy: 'prescriptions')]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Diagnostique $diagnostique = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDatePrscription(): ?\DateTimeInterface
    {
        return $this->datePrscription;
    }

    public function setDatePrscription(\DateTimeInterface $datePrscription): static
    {
        $this->datePrscription = $datePrscription;

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

    public function getDiagnostique(): ?Diagnostique
    {
        return $this->diagnostique;
    }

    public function setDiagnostique(?Diagnostique $diagnostique): static
    {
        $this->diagnostique = $diagnostique;

        return $this;
    }
}
