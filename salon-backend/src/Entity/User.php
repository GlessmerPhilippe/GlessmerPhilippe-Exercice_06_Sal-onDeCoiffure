<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
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

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $civilite = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $tel = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, CoiffeurHoraire>
     */
    #[ORM\OneToMany(targetEntity: CoiffeurHoraire::class, mappedBy: 'coiffeur')]
    private Collection $coiffeurHoraires;

    /**
     * @var Collection<int, RendezVous>
     */
    #[ORM\OneToMany(targetEntity: RendezVous::class, mappedBy: 'client')]
    private Collection $rendezVouses;

    public function __construct()
    {
        $this->coiffeurHoraires = new ArrayCollection();
        $this->rendezVouses = new ArrayCollection();
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

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): static
    {
        $this->tel = $tel;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, CoiffeurHoraire>
     */
    public function getCoiffeurHoraires(): Collection
    {
        return $this->coiffeurHoraires;
    }

    public function addCoiffeurHoraire(CoiffeurHoraire $coiffeurHoraire): static
    {
        if (!$this->coiffeurHoraires->contains($coiffeurHoraire)) {
            $this->coiffeurHoraires->add($coiffeurHoraire);
            $coiffeurHoraire->setCoiffeur($this);
        }

        return $this;
    }

    public function removeCoiffeurHoraire(CoiffeurHoraire $coiffeurHoraire): static
    {
        if ($this->coiffeurHoraires->removeElement($coiffeurHoraire)) {
            // set the owning side to null (unless already changed)
            if ($coiffeurHoraire->getCoiffeur() === $this) {
                $coiffeurHoraire->setCoiffeur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RendezVous>
     */
    public function getRendezVouses(): Collection
    {
        return $this->rendezVouses;
    }

    public function addRendezVouse(RendezVous $rendezVouse): static
    {
        if (!$this->rendezVouses->contains($rendezVouse)) {
            $this->rendezVouses->add($rendezVouse);
            $rendezVouse->setClient($this);
        }

        return $this;
    }

    public function removeRendezVouse(RendezVous $rendezVouse): static
    {
        if ($this->rendezVouses->removeElement($rendezVouse)) {
            // set the owning side to null (unless already changed)
            if ($rendezVouse->getClient() === $this) {
                $rendezVouse->setClient(null);
            }
        }

        return $this;
    }
}
