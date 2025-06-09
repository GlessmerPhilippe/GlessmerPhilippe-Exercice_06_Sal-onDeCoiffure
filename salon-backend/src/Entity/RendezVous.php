<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
#[ORM\Table(
    name: 'rendez_vous',
    uniqueConstraints: [
        new UniqueConstraint(
            name: 'rdv_unique_coiffeur_date_heure',
            columns: ['coiffeur_id', 'date', 'heure_debut']
        )
    ]
)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    private ?User $coiffeur = null;

    #[ORM\ManyToOne(inversedBy: 'rendezVouses')]
    private ?Prestation $prestation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $heureDebut = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getCoiffeur(): ?User
    {
        return $this->coiffeur;
    }

    public function setCoiffeur(?User $coiffeur): static
    {
        $this->coiffeur = $coiffeur;

        return $this;
    }

    public function getPrestation(): ?Prestation
    {
        return $this->prestation;
    }

    public function setPrestation(?Prestation $prestation): static
    {
        $this->prestation = $prestation;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeureDebut(): ?\DateTime
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(\DateTime $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
