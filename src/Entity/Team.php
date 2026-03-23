<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
#[UniqueEntity('name', message:'Une équipe similaire est déjà enregistré')]
#[UniqueEntity('codeCompetition', message:'Ce code de compétition est déjà enregistré')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeCompetition = null;

    #[ORM\Column(length: 180)]
    private ?string $Name = null;

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
        return $this->Name;
    }

    public function setName(string $Name): static
    {
        $this->Name = $Name;

        return $this;
    }
}
