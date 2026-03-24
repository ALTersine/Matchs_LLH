<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[UniqueEntity('name', message:'Une équipe similaire est déjà enregistré')]
#[UniqueEntity(['codeCompetition','codePoule'], message:'Ce code de compétition et code Poule sont déjà utilisé')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeCompetition = null;

    #[ORM\Column(length: 180)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codePoule = null;
    
    public function __construct(string $competition, string $equipe)
    {
        $this->codeCompetition = $competition;
        $this->name = $equipe;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeCompetition(): ?string
    {
        return $this->codeCompetition;
    }

    public function setCodeCompetition(string $codeCompetition): static
    {
        $this->codeCompetition = $codeCompetition;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCodePoule(): ?string
    {
        return $this->codePoule;
    }

    public function setCodePoule(?string $codePoule): static
    {
        $this->codePoule = $codePoule;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
