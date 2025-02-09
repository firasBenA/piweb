<?php

namespace App\Entity;

use App\Repository\ConsultationHisRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationHisRepository::class)]
class ConsultationHis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column(length: 255)]
    private ?string $notesMed = null;

    #[ORM\Column(length: 255)]
    private ?string $traitement = null;

    #[ORM\Column]
    private ?int $duree = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Consultation $consultation = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

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

    public function getNotesMed(): ?string
    {
        return $this->notesMed;
    }

    public function setNotesMed(string $notesMed): static
    {
        $this->notesMed = $notesMed;

        return $this;
    }

    public function getTraitement(): ?string
    {
        return $this->traitement;
    }

    public function setTraitement(string $traitement): static
    {
        $this->traitement = $traitement;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): static
    {
        $this->duree = $duree;

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
}
