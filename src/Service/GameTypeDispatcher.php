<?php

namespace App\Service;

use App\Exception\CsvException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GameTypeDispatcher
{

    private const array REQUIRED_COLUMNS = [
        'competition',
        'poule',
        'le',
        'horaire',
        'club rec',
        'club vis',
        'code renc'
    ];

    private const array RESULTS_COLUMNS = [
        'sc rec',
        'sc vis',
        'Etat',
        'Forfait'
    ];

    public function isImportingGamesPreview(string $directory): bool
    {
        $csv = fopen($directory, 'r');

        if(!$csv){
            throw new CsvException('Fichier introuvable, ou impossible à ouvrir', 400);
        }

        try{
            $header = fgetcsv($csv, separator:';', escape:'');
            $i = 0;

            foreach(self::REQUIRED_COLUMNS as $headerRequired){
                if(!in_array($headerRequired,$header, true)){
                    throw new CsvException('Colonne ' . $headerRequired . ' manquante', 400);
                }
            }

            foreach(self::RESULTS_COLUMNS as $headerForResultats){
                if(in_array($headerForResultats, $header, true)){
                    ++$i;
                }
            }

            if($i===0){
                return true;
            }elseif ($i === count(self::RESULTS_COLUMNS)){
                return false;
            }else {
                throw new CsvException('Erreur au traitement du fichier de résultat.');
            }

        }finally{
            fclose($csv);
        }
    }
}
