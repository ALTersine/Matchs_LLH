<?php

namespace App\Service;

use App\Entity\Game;
use App\Exception\GameException;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GameFactory
{

    public function __construct(
        private readonly GameTypeDispatcher $serviceCSV,
        private readonly ContainerBagInterface $container,
        private readonly GameRepository $repo,
        private readonly FindTeam $serviceTeam,
        private readonly EntityManagerInterface $em
    ) {}

    public function createGames(array $files): array
    {
        $anouncment = [];

        foreach ($files as $file) {
            $games = null;
            $importResults = $this->serviceCSV->processCSVImport($file);

            switch ($importResults['type']) {
                case $this->container->get('app.label.game.absent'):
                    break;
                case $this->container->get('app.label.game.preview'):
                    $games = $this->persistingGames($importResults['games'], false);
                    break;
                case $this->container->get('app.label.game.result'):
                    $games = $this->persistingGames($importResults['games'], true);
                    break;
                default:
                    throw new GameException(
                        'Traitement du type de fichier erroné. Impossible de créer les matchs'
                    );
            }

            $anouncment[] = $games;
        }
        return $anouncment;
    }

    /** Fonctions pour créer ou mettre à jour les matchs en base
     *  + Alimente le tableau retrouné avec les codes rencontre des matchs à utiliser dans la création d'image
     */
    private function persistingGames(array $data, bool $isAResult): array
    {
        $toImplement = [];
        $toImplement['isResult'] = $isAResult;

        foreach ($data as $gameData) {
            $idGame = $gameData['code renc'];
            $game = $this->createGameIfDoesNotExist($idGame);

            try {
                $this->settingGameRequiredData($gameData, $game);

                if ($isAResult) {
                    $this->settingGameResultsData($gameData, $game);
                }

                $this->em->persist($game);
                $toImplement[] = $idGame;
            } catch (GameException $e) {
                throw new GameException($e->getMessage());
            }
        }
        $this->em->flush();
        return $toImplement;
    }

    /** Fonctions pour factoriser l'idratation des objets Match
     *  + Qualification de la bonne équipe du landeau ici
     */

    private function createGameIfDoesNotExist(string $code): Game
    {
        $game = $this->repo->findWithCodeRenc($code);

        if (!$game) {
            $game = new Game();
            $game->setCodeRenc($code);
        }

        return $game;
    }

    private function settingGameRequiredData(array $data, Game $game): void
    {
        $game->setCompetition($data['competition']);
        $game->setPoule($data['poule']);
        $game->setDate($data['le']);
        $game->setHeure($data['horaire']);

        if (str_contains($data['club rec'], 'LANDREAU')) {
            $game->setClubExterieur($data['club vis']);
            $game->setClubADomicile(
                $this->serviceTeam->getTeamName($data['competition'], $data['poule'])
            );
        } elseif (str_contains($data['club vis'], 'LANDREAU')) {
            $game->setClubExterieur(
                $this->serviceTeam->getTeamName($data['competition'], $data['poule'])
            );
            $game->setClubADomicile($data['club rec']);
        } else {
            $game->setClubExterieur($data['club vis']);
            $game->setClubADomicile($data['club rec']);
        }

        $game->setHosting();
    }

    private function settingGameResultsData(array $data, Game $game): void
    {
        $game->setScoreADomicile((int)$data['sc rec']);
        $game->setScoreExterieur((int)$data['sc vis']);
        $game->setEtat($data['Etat']);
        $game->setForfait($data['Forfait']);
    }
}
