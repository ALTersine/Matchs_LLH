<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[UniqueEntity('codeRenc', message: 'le code de la rencontre est déjà utilisé.')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $codeRenc = null;

    #[ORM\Column(length: 255)]
    private ?string $competition = null;

    #[ORM\Column(length: 255)]
    private ?string $poule = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $heure = null;

    #[ORM\Column(length: 255)]
    private ?string $clubADomicile = null;

    #[ORM\Column(length: 255)]
    private ?string $clubExterieur = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $scoreADomicile = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $scoreExterieur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forfait = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeRenc(): ?string
    {
        return $this->codeRenc;
    }

    public function setCodeRenc(string $codeRenc): static
    {
        $this->codeRenc = $codeRenc;

        return $this;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function setCompetition(string $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getPoule(): ?string
    {
        return $this->poule;
    }

    public function setPoule(string $poule): static
    {
        $this->poule = $poule;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?\DateTimeImmutable
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeImmutable $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getClubADomicile(): ?string
    {
        return $this->clubADomicile;
    }

    public function setClubADomicile(string $clubADomicile): static
    {
        $this->clubADomicile = $clubADomicile;

        return $this;
    }

    public function getClubExterieur(): ?string
    {
        return $this->clubExterieur;
    }

    public function setClubExterieur(string $clubExterieur): static
    {
        $this->clubExterieur = $clubExterieur;

        return $this;
    }

    public function getScoreADomicile(): ?int
    {
        return $this->scoreADomicile;
    }

    public function setScoreADomicile(int $scoreADomicile): static
    {
        $this->scoreADomicile = $scoreADomicile;

        return $this;
    }

    public function getScoreExterieur(): ?int
    {
        return $this->scoreExterieur;
    }

    public function setScoreExterieur(int $scoreExterieur): static
    {
        $this->scoreExterieur = $scoreExterieur;

        return $this;
    }

    public function getForfait(): ?string
    {
        return $this->forfait;
    }

    public function setForfait(?string $forfait): static
    {
        $this->forfait = $forfait;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(?string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**Les fonctions métiers*/
    public function isADomicile(): bool
    {
        return str_contains($this->clubADomicile, "LE LANDREAU HANDBALL") ? true : false;
    }

    public function isGamePreview(): bool
    {
        return $this->scoreADomicile === null;
    }

    public function isASundayGame(Game $compared): bool {
        return $this->date > $compared->getDate();
    }
}
