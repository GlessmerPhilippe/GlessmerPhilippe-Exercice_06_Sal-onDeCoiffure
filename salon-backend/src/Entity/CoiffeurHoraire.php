<?php

namespace App\Entity;

use App\Repository\CoiffeurHoraireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoiffeurHoraireRepository::class)]
class CoiffeurHoraire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'coiffeurHoraires')]
    private ?User $coiffeur = null;

    #[ORM\Column(nullable: true)]
    private ?int $jourSemaine = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $heureDebut = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $heureFin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getJourSemaine(): ?int
    {
        return $this->jourSemaine;
    }

    public function setJourSemaine(?int $jourSemaine): static
    {
        $this->jourSemaine = $jourSemaine;

        return $this;
    }

    public function getHeureDebut(): ?\DateTime
    {
        return $this->heureDebut;
    }

    public function setHeureDebut(?\DateTime $heureDebut): static
    {
        $this->heureDebut = $heureDebut;

        return $this;
    }

    public function getHeureFin(): ?\DateTime
    {
        return $this->heureFin;
    }

    public function setHeureFin(?\DateTime $heureFin): static
    {
        $this->heureFin = $heureFin;

        return $this;
    }
}
