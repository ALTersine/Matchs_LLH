<?php

namespace App\Service;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class FindTeam
{
    /** Équipes créées automatiquement pendant l'import, en attente du flush */
    private array $createdTeams = [];

    public function __construct(
        private readonly TeamRepository $repo,
        private readonly EntityManagerInterface $em
    ) {}

    public function getTeamName(string $competition, string $poule): string
    {
        $teamFromCompetition = $this->repo->findBy(['codeCompetition' => $competition]);
        if (count($teamFromCompetition) === 0 || !$teamFromCompetition) {
            return $this->createTeamFromCompetition($competition);
        } elseif (count($teamFromCompetition) === 1) {
            return $teamFromCompetition[0];
        } else {
            $teamFromPoule = $this->repo->findOneBy(['codePoule' => $poule]);
            if (!$teamFromPoule) {
                throw new Exception(
                    'L\'équipe n\'a pas été trouvée pour la poule ' . $poule
                );
            }
            return $teamFromPoule;
        }
    }

    /** Création automatique d'une équipe jeune inconnue en base
     *  ex : u17m-44 => U17 Masculins, u15f-44 => U15 Féminins
     */
    private function createTeamFromCompetition(string $competition): string
    {
        if (isset($this->createdTeams[$competition])) {
            return $this->createdTeams[$competition];
        }

        if (!preg_match('/^u(\d+)([mf])/i', $competition, $matches)) {
            throw new Exception(
                'L\'équipe n\'a pas été trouvée pour la compétition ' . $competition
            );
        }

        $genre = strtolower($matches[2]) === 'm' ? 'Masculins' : 'Féminins';
        $team = new Team($competition, 'U' . $matches[1] . ' ' . $genre);

        //Le flush est fait par GameFactory à la fin de l'import
        $this->em->persist($team);
        $this->createdTeams[$competition] = $team;

        return $team;
    }
}
