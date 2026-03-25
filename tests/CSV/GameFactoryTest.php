<?php

namespace App\Tests;

use App\Exception\CsvException;
use App\Service\GameFactory;
use App\Service\GameTypeDispatcher;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GameFactoryTest extends KernelTestCase
{
    private GameFactory $service;
    private ContainerBagInterface $container;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer()->get(ContainerBagInterface::class);
        $this->service =  static::getContainer()->get(GameFactory::class);
    }

    private function getCsv(string $name): string
    {
        return $this->container->get('app.test_import_directory') . '/' . $name;
    }

    public function testCreateGames(): void
    {
        //faire les variables de tests avec les codes à valider et les noms des fichiers
        //Lancer l'appel à createGames
        //assert pour valider qu'on récupère en 2 sous tableau, un de résultat, un de préview et tester les codes reçus.
    }
}
