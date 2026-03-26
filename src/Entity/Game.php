<?php

namespace App\Entity;

use App\Repository\GameRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[UniqueEntity('codeRenc', message: 'le code de la rencontre est déjà utilisé.')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[NotBlank()]
    private ?string $codeRenc = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    private ?string $competition = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    private ?string $poule = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[NotBlank()]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[NotBlank()]
    private ?\DateTimeImmutable $heure = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    private ?string $clubADomicile = null;

    #[ORM\Column(length: 255)]
    #[NotBlank()]
    private ?string $clubExterieur = null;

    #[ORM\Column]
    private ?bool $hosting = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $scoreADomicile = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $scoreExterieur = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $forfait = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat = null;

    public function __construct()
    {
        $this->hosting = false;
    }
    
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

    public function setDate(string $date): static
    {
        $this->date = DateTimeImmutable::createFromFormat("d/m/Y", $date);

        return $this;
    }

    public function getHeure(): ?\DateTimeImmutable
    {
        return $this->heure;
    }

    public function setHeure(string $heure): static
    {
        $this->heure = DateTimeImmutable::createFromFormat("H:i:s", $heure);

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

    public function isHosting(): ?bool
    {
        return $this->hosting;
    }

    public function setHosting(): static
    {
        $this->hosting = $this->isADomicile();

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

    /**Getter en format de display (string) */
    public function displayDate() : string {
        return $this->date?->format("d/m/Y");
    }

    public function displayHour() : string {
        return $this->heure?->format("d/m/Y");
    }

    public function displayScoreVis() :string {
        return ((string) $this->scoreExterieur);
    }

    public function displayScoreRec() :string {
        return ((string) $this->scoreADomicile);
    }

    /**Les fonctions sur mesure*/
    public function isADomicile(): bool
    {
        return str_contains($this->clubADomicile, "LE LANDREAU HANDBALL") ? true : false;
    }

    public function isGamePreview(): bool
    {
        return $this->scoreADomicile === null;
    }

    public function gameWeekDay(): string
    {
        switch ($this->getDate()->format('N')) {
            case 6:
                return 'saturday';
            case 7:
                return 'sunday';
            default:
                return 'not on weekend';
        }
    }

    public function winner(): string
    {
        if ($this->forfait !== null) {
            return "forfait";
        } elseif ($this->getScoreADomicile() === $this->getScoreExterieur()) {
            return "match null";
        } else {
            return $this->getScoreADomicile() > $this->getScoreExterieur()
                ? $this->getClubADomicile()
                : $this->getClubExterieur();
        }
    }
}
