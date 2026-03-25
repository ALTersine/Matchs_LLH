<?php

namespace App\Service;

use App\Repository\TeamRepository;
use Exception;

class FindTeam
{

    public function __construct(
        private readonly TeamRepository $repo
    ) {}

    public function getTeamName(string $competition, string $poule): string
    {
        $teamFromCompetition = $this->repo->findBy(['codeCompetition' => $competition]);
        if (count($teamFromCompetition) === 0 || !$teamFromCompetition) {
            throw new Exception(
                'L\'équipe n\'a pas été trouvée pour la compétition ' . $competition
            );
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
}
