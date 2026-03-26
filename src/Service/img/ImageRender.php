<?php

namespace App\Service\img;

use App\Entity\Game;
use Exception;
use GdImage;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ImageRender
{
    private const int ROW_HEIGHT    = 100;
    private const int ROW_START_X   = 87;
    private const int ROW_END_X     = 962;

    private const int SAT_HEADER_X    = 826;
    private const int SAT_HEADER_Y    = 275;
    private const int SAT_FIRST_ROW_Y = 364;
    private const int SAT_MAX_ROWS    = 9;

    private const int SUN_HEADER_X    = 826;
    private const int SUN_HEADER_Y    = 756;
    private const int SUN_FIRST_ROW_Y = 830;
    private const int SUN_MAX_ROWS    = 5;

    private const int COL1_CENTER_X = 87;
    private const int COL2_START_X  = 130;
    private const int COL2_END_X    = 509;
    private const int COL3_START_X  = 510;
    private const int COL3_END_X    = 589;
    private const int COL4_START_X  = 590;
    private const int COL4_END_X    = 962;

    private const int FONT_SIZE     = 16;
    private const int ICON_SIZE     = 45;

    private string $font = '';

    public function __construct(
        private readonly ContainerBagInterface $container
    ) {
        $this->font = $this->getFont();
    }

    /** Méthode principale qui va créer les les tableaux des matchs */
    public function drawGames(GdImage $canvas, array $saturdayGames, array $sundayGames, bool $isResult): void
    {
        if (!empty($saturdayGames)) {
            //Réalisation du header
            $this->drawHeader($canvas, self::SAT_HEADER_X, self::SAT_HEADER_Y, 'saturday');
            $y = self::SAT_FIRST_ROW_Y;

            foreach ($saturdayGames as $game) {
                //Réalisation de la ligne de match
                $this->drawRow($canvas, $y, $game, $isResult);
                $y += self::ROW_HEIGHT;
            }
        }

        if (!empty($sundayGames)) {
            //Réalisation du header
            $this->drawHeader($canvas, self::SUN_HEADER_X, self::SUN_HEADER_Y, 'sunday');
            $y = self::SUN_FIRST_ROW_Y;

            foreach ($sundayGames as $game) {
                //Réalisation de la ligne de match
                $this->drawRow($canvas, $y, $game, $isResult);
                $y += self::ROW_HEIGHT;
            }
        }
    }

    /**Mes méthodes qui créent la structure du tableau à savoir
     * le header qui dessine soit l'icone soit dimanche soit samedi 
     * les lignes générés soit pour une anonce de match soit pour un résultat
     */

    private function drawHeader(GdImage $canvas, int $x, int $y, string $day): void
    {
        $this->drawIcon($canvas, $day . '.png', $x, $y, centered: true);
    }

    private function drawRow(GdImage $canvas, int $y, Game $game, bool $isResult): void
    {
        //Gestion de l'icone domicile ou extérieure
        $icon = $game->isHosting() ? 'home.png' : 'out.png';
        $iconX = self::COL1_CENTER_X - (int)(self::ICON_SIZE / 2);
        $iconY = $y + (int)((self::ROW_HEIGHT - self::ICON_SIZE) / 2);
        $this->drawIcon($canvas, $icon, $iconX, $iconY, centered: false, size: self::ICON_SIZE);

        if ($isResult) {
            $this->drawGameResult($canvas, $y, $game);
        } else {
            $this->drawGamePreview($canvas, $y, $game);
        }
    }

    /** Méthode dédiée aux lignes des tableaux */
    private function drawGamePreview(GdImage $canvas, int $y, Game $game) : void
    {
        //Info club recevant ou club à domicile
        $clubRec = $this->clubName($game, isRec: true);
        $this->drawGameInfoInRow($canvas, 2, $clubRec, $y);

        //horaire du match
        $horaire = (string) $game->getHeure();
        $this->drawGameInfoInRow($canvas, 3, $horaire, $y, true);

        //Info club extérieur ou club visiteur
        $clubVis = $this->clubName($game, isRec: false);
        $this->drawGameInfoInRow($canvas, 4, $clubVis, $y);
    }

    private function drawGameResult(GdImage $canvas, int $y, Game $game) :void
    {
        if ($game->getEtat() !== 'JOUE') {
            return;
        }

        $winner = $game->winner();
        $clubRec = $this->clubName($game, isRec: true);
        $clubVis = $this->clubName($game, isRec: false);

        switch ($winner) {
            case 'forfait':
                $this->drawGameInfoInRow($canvas, 2, $clubRec, $y);
                $this->drawGameInfoInRow($canvas, 3, $winner, $y, true);
                $this->drawGameInfoInRow($canvas, 4, $clubVis, $y);
                break;

            case 'match null':
                $this->drawGameInfoInRow($canvas, 2, $clubRec, $y);
                $score = $this->formatScore($game);
                $this->drawGameInfoInRow($canvas, 3, $score, $y, true);
                $this->drawGameInfoInRow($canvas, 4, $clubVis, $y);
                break;

            default:
                $this->drawGameInfoInRow($canvas, 2, $clubRec, $y, $winner === $game->getClubADomicile());
                $score = $this->formatScore($game);
                $this->drawGameInfoInRow($canvas, 3, $score, $y, true);
                $this->drawGameInfoInRow($canvas, 4, $clubVis, $y, $winner === $game->getClubExterieur());
                break;
        }
    }

    /** Les Utils à savoir
     * La création de l'icon
     * La création des infos du matchs dans les lignes du tableau
     * La génération du score pour l'affichage
     * La génération du nom du club et l'outil de truncate s'il est trop long
     * Calcul des alignements justify center et justify end et du Y des lignes
     * Les couleurs et la font
     */

    private function drawIcon(GdImage $canvas, string $name, int $x, int $y, bool $centered, int $size = 0): void
    {
        $iconPath = $this->container->get('app.public_img') . '/' . $name;
        if (!file_exists($iconPath)) {
            throw new Exception('Impossible de récupérer l\'icone : ' . $name);
        }

        $icon = imagecreatefrompng($iconPath);
        $iconW = imagesx($icon);
        $iconH = imagesy($icon);

        if ($size > 0) {
            $destW = $size;
            $destH = $size;
        } else {
            $destW = imagesx($icon);
            $destH = imagesy($icon);
        }

        $destX = $centered ? $x - (int)($destW / 2) : $x;

        imagecopyresampled($canvas, $icon, $destX, $y, 0, 0, $destW, $destH, $iconW, $iconH);
    }

    private function drawGameInfoInRow(GdImage $canvas, int $colNum, string $info, int $y, bool $isyellow = false): void
    {
        $color = $isyellow ? $this->yellow($canvas) : $this->white($canvas);
        $textX = null;
        $textY = $this->getTextY($y);
        $font = $this->font;

        switch ($colNum) {
            case 2:
                $textX = $this->getJustifyEndX($info, self::COL2_END_X, $font);
                break;
            case 3:
                $textX = $this->getCenterX($info, self::COL3_START_X, self::COL3_END_X, $font);
                break;
            case 4:
                $textX = self::COL4_START_X;
                break;
            default:
                throw new Exception('Erreur à la transmission du numéro de colonne de ' . $info);
        }

        imagettftext(
            $canvas,
            self::FONT_SIZE,
            0,
            $textX,
            $textY,
            $color,
            $font,
            $info
        );
    }

    //Util Score
    private function formatScore(Game $game): string
    {
        return ((string)$game->getScoreADomicile()) . ' - ' . ((string)$game->getScoreExterieur());
    }

    //Utils Nom d'équipe et Truncate
    private function clubName(Game $game, bool $isRec): string
    {
        if ($isRec) {
            return $this->truncateText($game->getClubADomicile(), self::COL2_START_X, self::COL2_END_X);
        } else {
            return $this->truncateText($game->getClubExterieur(), self::COL4_START_X, self::COL4_END_X);
        }
    }

    private function truncateText(string $name, int $startX, int $endX) : string {
        $maxW = $endX - $startX;
        $bbox = imagettfbbox(self::FONT_SIZE, 0, $this->font, $name);

        if (abs($bbox[4] - $bbox[0]) <= $maxW) {
            return $name;
        }

        while (mb_strlen($name) > 0) {
            $name = mb_substr($name, 0, mb_strlen($name) - 1);
            $truncated = $name . '...';
            $bbox = imagettfbbox(self::FONT_SIZE, 0, $this->font, $truncated);

            if (abs($bbox[4] - $bbox[0]) <= $maxW) {
                return $truncated;
            }
        }

        return '...';
    }

    //Utils de calcul X et Y
    private function getJustifyEndX(string $text, int $endX, string $font) : int
    {
        $bbox = imagettfbbox(self::FONT_SIZE, 0, $font, $text);
        return $endX - abs($bbox[4] - $bbox[0]);
    }

    private function getCenterX(string $text, int $startX, int $endX, string $font) : int
    {
        $bbox = imagettfbbox(self::FONT_SIZE, 0, $font, $text);
        $textW = abs($bbox[4] - $bbox[0]);
        $zoneW = $endX - $startX;

        return $startX + (int)(($zoneW - $textW) / 2);
    }

    private function getTextY(int $y): int
    {
        return $y + (int)(self::ROW_HEIGHT / 2) + (int)(self::FONT_SIZE / 2);
    }

    //Utils couleurs et font
    private function white(GdImage $canvas): int
    {
        return imagecolorallocate($canvas, 255, 255, 255);
    }

    private function yellow(GdImage $canvas): int
    {
        return imagecolorallocate($canvas, 252, 213, 0);
    }

    private function getFont() : string {
        return $this->container->get('app.public_font');
    }
}
