<?php

namespace App\Service;

use DateTimeImmutable;

class SocialMediaOutLines
{
    private DateTimeImmutable $today;

    public function __construct()
    {
        $this->today = new DateTimeImmutable('now');
    }

    public function outLinesForResults(): string
    {
        $saturday = $this->today->modify('last saturday');
        $sunday = $this->today->modify('last sunday');

        return 'Découvrez le(s) résultat(s) du ' . $this->gettingDateSequence($saturday, $sunday) . ' . Pour plus d\'informations : https://lelandreauhandball.com';
    }

    public function outLinesForPreview(): string
    {
        $saturday = $this->today->modify('next saturday');
        $sunday = $this->today->modify('next sunday');

        return 'Découvrez le(s) match(s) du ' . $this->gettingDateSequence($saturday, $sunday) . ' . Pour plus d\'informations : https://lelandreauhandball.com';
    }

    private function gettingDateSequence(DateTimeImmutable $saturday, DateTimeImmutable $sunday): string
    {
        $satDay = $saturday->format('d');
        $sunDay = $sunday->format('d');

        $satMonth = $this->monthFrenchName($saturday->format('m'));
        $sunMonth = $this->monthFrenchName($sunday->format('m'));

        return $satDay . ($satMonth === $sunMonth ? '' : $satMonth) . ' et ' . $sunDay . ' ' . $sunMonth;
    }

    private function monthFrenchName(string $month): string
    {
        switch ($month) {
            case "01":
                return "janvier";
            case "02":
                return "février";
            case "03":
                return "mars";
            case "04":
                return "avril";
            case "05":
                return "mai";
            case "06":
                return "juin";
            case "07":
                return "juillet";
            case "08":
                return "août";
            case "09":
                return "septembre";
            case "10":
                return "octobre";
            case "11":
                return "novembre";
            case "12":
                return "décembre";
            default:
                return ' ';
        }
    }
}
