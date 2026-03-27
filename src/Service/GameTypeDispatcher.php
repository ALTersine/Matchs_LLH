<?php

namespace App\Service;

use App\Exception\CsvException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GameTypeDispatcher
{

public function __construct(
    private readonly ContainerBagInterface $container
)
{
}

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
    

    public function processCSVImport(string $directory): array
    {
        $csv = fopen($directory, 'r');

        if ($csv === false) {
            throw new CsvException('Fichier introuvable, ou impossible à ouvrir', 400);
        }

        try {
            $header = fgetcsv($csv, separator: ';', escape: '');

            //Si le fichier est vide, on renvoie aucun match dans le type
            if ($header === false) {
                return ['type' => $this->container->get('app.label.game.absent')];
            }

            //Vérification qu'il a tout ce qu'il faut à minima pour l'import des preview
            foreach (self::REQUIRED_COLUMNS as $headerRequired) {
                if (!in_array($headerRequired, $header, true)) {
                    throw new CsvException('Colonne ' . $headerRequired . ' manquante', 400);
                }
            }

            //On compte le nombre de colonne utilisé pour un résultat.
            $i = 0;
            foreach (self::RESULTS_COLUMNS as $headerForResultats) {
                if (in_array($headerForResultats, $header, true)) {
                    ++$i;
                }
            }

            //Si on a trouvé aucune colonne de résultt, c'est un import de preview
            if ($i === 0) {
                return $this->gamesData($csv, $header, self::REQUIRED_COLUMNS, $this->container->get('app.label.game.preview'));
            
            //Si on a bient toute les colonnes supplémentaires pour un résultat, c'est l'import de result
            } elseif ($i === count(self::RESULTS_COLUMNS)) {
                $allColumns = array_merge(self::REQUIRED_COLUMNS, self::RESULTS_COLUMNS);
                return $this->gamesData($csv, $header, $allColumns, $this->container->get('app.label.game.result'));

            //Si aucun compte n'est correct, on lève une exception
            } else {
                throw new CsvException('Erreur au traitement du fichier de résultat.');
            }
        } finally {
            fclose($csv);
        }
    }

    private function gamesData($file, array $header, array $neededHeader, string $type): array
    {
        $data = [];
        //Retirer les espaces dans les nom de colonnes
        $cleanHeader = $this->cleanHeader($header);
        $cleanHeaderNeeded = $this->cleanHeader($neededHeader);

        while (($row = fgetcsv($file, separator: ";", escape: '')) !== false) {
            $row = array_pad($row, count($header), null);
            $allData = array_combine($cleanHeader, $row);

            $data[] = $this->cleanData($allData, $cleanHeaderNeeded);
        }
        return [
            'type' => $type,
            'games' => $data
        ];
    }

    private function cleanData(array $allData, array $neededHeader): array
    {
        $data = [];
        foreach ($neededHeader as $column) {
            $data[$column] = trim($allData[$column]);
        }
        return $data;
    }

    private function cleanHeader(array $header) :array{
        $cleanHeader = [];
        foreach($header as $name){
            $cleanHeader[] = str_replace(' ','_',$name);
        }
        return $cleanHeader;
    }
}
