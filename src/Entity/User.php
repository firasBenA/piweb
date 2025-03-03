<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use PhpParser\Node\Expr\Cast\String_;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'il existe déjà un compte avec cet email.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, EmailTwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'L\'email est requis.')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas un email valide.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]

    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre nom.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre prénom.')]
    #[Assert\Length(min: 2, max: 50, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.', maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $prenom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez entrer votre âge.')]
    #[Assert\Regex(pattern: '/^\d+$/', message: 'L\'âge doit être un nombre entier.')]
    private ?int $age = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre adresse.')]
    private ?string $adresse = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez choisir votre sexe.')]
    private ?string $sexe = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre numéro de téléphone.')]
    #[Assert\Regex(pattern: '/^\d{8}$/', message: 'Le numéro de téléphone doit contenir exactement 8 chiffres.')]
    private ?string $telephone = null; // ✅ Make sure it's "string", not "String"


    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png'],
        mimeTypesMessage: 'Veuillez télécharger une image au format JPEG ou PNG.'
    )]
    private ?string $imageProfil = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre spécialité.', groups: ['medecin'])]
    private ?string $specialite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\File(
        maxSize: '5M',
        mimeTypes: ['image/jpeg', 'image/png'],
        mimeTypesMessage: 'Veuillez télécharger une image au format JPEG ou PNG.',
        groups: ['medecin']
    )]
    #[Assert\NotBlank(message: 'Veuillez télécharger votre certificat.', groups: ['medecin'])]
    private ?string $certificat = null;

    #[ORM\OneToOne(targetEntity: DossierMedical::class, mappedBy: 'user')]
    private ?DossierMedical $dossierMedical = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RendezVous::class)]
    private Collection $rendezVouses;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: 'App\Entity\Consultation')]
    private Collection $consultations;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;

    public function __construct()
    {
        $this->rendezVouses = new ArrayCollection();
        $this->consultations = new ArrayCollection();
        $this->createdAt = new \DateTime();

    }


    #[ORM\Column(type: 'boolean')]
    private $emailAuthEnabled = true;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $emailAuthCode = null;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $failedLoginAttempts = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lockUntil = null;

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function setFailedLoginAttempts(int $attempts): self
    {
        $this->failedLoginAttempts = $attempts;
        return $this;
    }

    public function getLockUntil(): ?\DateTimeInterface
    {
        return $this->lockUntil;
    }

    public function setLockUntil(?\DateTimeInterface $lockUntil): self
    {
        $this->lockUntil = $lockUntil;
        return $this;
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    public function isBlocked(): bool
    {
        return in_array('ROLE_BLOCKED', $this->getRoles());
    }
    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getImageProfil(): ?string
    {
        return $this->imageProfil;
    }

    public function setImageProfil(string $imageProfil): static
    {
        $this->imageProfil = $imageProfil;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getCertificat(): ?string
    {
        return $this->certificat;
    }

    public function setCertificat(string $certificat): static
    {
        $this->certificat = $certificat;

        return $this;
    }

    public function getDossierMedical(): ?DossierMedical
    {
        return $this->dossierMedical;
    }

    public function setDossierMedical(?DossierMedical $dossierMedical): self
    {
        $this->dossierMedical = $dossierMedical;

        return $this;
    }

    public function getRendezVouses(): Collection
    {
        return $this->rendezVouses;
    }

    public function addRendezVouse(RendezVous $rendezvous): self
    {
        if (!$this->rendezVouses->contains($rendezvous)) {
            $this->rendezVouses[] = $rendezvous;
            $rendezvous->setUser($this); // set the user in the rendezvous
        }

        return $this;
    }

    public function removeRendezVouse(RendezVous $rendezvous): self
    {
        $this->rendezVouses->removeElement($rendezvous);
        // set the owning side to null (unless already changed)
        if ($rendezvous->getUser() === $this) {
            $rendezvous->setUser(null);
        }

        return $this;
    }

    public function getConsultations(): Collection
    {
        return $this->consultations;
    }

    public function addConsultation(Consultation $consultation): self
    {
        if (!$this->consultations->contains($consultation)) {
            $this->consultations[] = $consultation;
            $consultation->setUser($this);
        }

        return $this;
    }

    public function removeConsultation(Consultation $consultation): self
    {
        if ($this->consultations->removeElement($consultation)) {
            if ($consultation->getUser() === $this) {
                $consultation->setUser(null);
            }
        }

        return $this;
    }

    public function getEmailAuthCode(): ?string
    {
        return $this->emailAuthCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->emailAuthCode = $authCode;
    }


    public function isEmailAuthEnabled(): bool
    {
        return $this->emailAuthEnabled;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }
}
