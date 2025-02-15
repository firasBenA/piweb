<?php

namespace App\Entity;

use App\Repository\SymptomesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SymptomesRepository::class)]
class Symptomes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dateApparition = null;

    #[ORM\Column(length: 255)]
    private ?string $zonesCorps = null;

    /**
     * @var Collection<int, Diagnostique>
     */
    #[ORM\ManyToMany(targetEntity: Diagnostique::class, mappedBy: 'symptomes')]
    private Collection $diagnostiques;

    public function __construct()
    {
        $this->diagnostiques = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDateApparition(): ?\DateTimeInterface
    {
        return $this->dateApparition;
    }

    public function setDateApparition(\DateTimeInterface $dateApparition): static
    {
        $this->dateApparition = $dateApparition;

        return $this;
    }

    public function getZonesCorps(): ?string
    {
        return $this->zonesCorps;
    }

    public function setZonesCorps(string $zonesCorps): static
    {
        $this->zonesCorps = $zonesCorps;

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
            $diagnostique->addSymptome($this);
        }

        return $this;
    }

    public function removeDiagnostique(Diagnostique $diagnostique): static
    {
        if ($this->diagnostiques->removeElement($diagnostique)) {
            $diagnostique->removeSymptome($this);
        }

        return $this;
    }
}
