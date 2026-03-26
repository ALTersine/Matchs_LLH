<?php

namespace App\Service\Img;

use App\Repository\GameRepository;
use Exception;

class ImageFactory
{
    private const int SAT_MAX_ROWS = 9;
    private const int SUN_MAX_ROWS = 5;

    public function __construct(
        private readonly GameRepository $repo,
        private readonly ImageGenerator $serviceGenerate,
        private readonly ImageRender $serviceHydrate
    ) {}

    public function createAnnouncments(array $gamesCode, bool $isResult): array
    {
        $games = $this->repo->findAllCodeRencsOrdered($gamesCode);

        if (empty($games)) {
            throw new Exception('Aucun match correspondant aux codes transmis');
        }

        $saturdayGames = array_values(array_filter($games, fn($g) => $g->gameWeekDay() === 'saturday'));
        $sundayGames = array_values(array_filter($games, fn($g) => $g->gameWeekDay() === 'sunday'));

        if (
            (count($saturdayGames) <= self::SAT_MAX_ROWS)
            &&
            (count($sundayGames) <= self::SUN_MAX_ROWS)
        ) {
            return [$this->generateOneAnnouncment($saturdayGames, $sundayGames, $isResult)];
        }

        return $this->generateMultipleAnnouncment($saturdayGames, $sundayGames, $isResult);
    }

    //Si tous les matchs tiennent dans une image on utilise cette fonction
    private function generateOneAnnouncment(array $saturday, array $sunday, bool $isResult): string
    {
        $canvas = $this->serviceGenerate->loadBackground($isResult);
        $this->serviceHydrate->drawGames($canvas, $saturday, $sunday, $isResult);
        return $this->serviceGenerate->saveAnnouncement($canvas, $isResult);
    }

    //Sinon on crée deux tableaux et on génère deux images
    private function generateMultipleAnnouncment(array $saturday, array $sunday, bool $isResult): array
    {
        $saturdayInitial = [];
        $saturdayExceeding = [];

        $sundayInitial = [];
        $sundayExceeding = [];

        $images = [];

        if (count($saturday) > self::SAT_MAX_ROWS) {
            $saturdayInitial = array_slice($saturday, 0, self::SAT_MAX_ROWS);
            $saturdayExceeding = array_slice($saturday, self::SAT_MAX_ROWS);
        } else {
            $saturdayInitial = $saturday;
        }

        if (count($sunday) > self::SUN_MAX_ROWS) {
            $sundayInitial = array_slice($sunday, 0, self::SUN_MAX_ROWS);
            $sundayExceeding = array_slice($sunday, self::SUN_MAX_ROWS);
        } else {
            $sundayInitial = $sunday;
        }

        $images[] = $this->generateOneAnnouncment($saturdayInitial, $sundayInitial, $isResult);
        $images[] = $this->generateOneAnnouncment($saturdayExceeding, $sundayExceeding, $isResult);

        return $images;
    }
}
