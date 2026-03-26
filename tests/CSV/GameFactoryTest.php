<?php

namespace App\Tests;

use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use App\Service\FindTeam;
use App\Service\GameFactory;
use App\Service\GameTypeDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class GameFactoryTest extends KernelTestCase
{
    private ContainerBagInterface $container;
    private GameTypeDispatcher $serviceCSV;
    private FindTeam $serviceTeam;
    private GameRepository $repo;
    private EntityManagerInterface $em;

    private GameFactory $service;

    private function createService(
        GameTypeDispatcher $serviceCSV,
        ContainerBagInterface $container,
        FindTeam $serviceTeam,
        GameRepository $repo,
        EntityManagerInterface $em
    ): GameFactory {
        return new GameFactory(
            $serviceCSV,
            $container,
            $repo,
            $serviceTeam,
            $em
        );
    }

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer()->get(ContainerBagInterface::class);
        $this->serviceCSV = new GameTypeDispatcher($this->container);
        $this->serviceTeam = new FindTeam(static::getContainer()->get(TeamRepository::class));
        $this->repo = static::getContainer()->get(GameRepository::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->service = $this->createService(
            $this->serviceCSV,
            $this->container,
            $this->serviceTeam,
            $this->repo,
            $this->em
        );
    }

    private function getCsv(string $name): string
    {
        return $this->container->get('app.test_import_directory') . '/' . $name;
    }

    public function testNoGame(): void
    {
        $fileTested[] = $this->getCsv('vide.csv');
        $resultat = $this->service->createGames($fileTested);

        $this->assertNull($resultat[0]);
    }

    public function testCreateGames(): void
    {
        //Vider la base avant pour être propre
        $historic = $this->repo->findAll();
        foreach ($historic as $oldTest) {
            $this->em->remove($oldTest);
        }

        //fichiers de test
        $fileTested[] = $this->getCsv('preview.csv');
        $fileTested[] = $this->getCsv('result.csv');

        $resultat = $this->service->createGames($fileTested);
        var_dump($resultat);

        $this->assertNotNull($resultat[0], 'Premier fichier à renvoyer un null');
        $this->assertNotEmpty($resultat[0], 'Premier fichier n\'a rien renvoyé');

        $this->assertNotNull($resultat[1], 'Second fichier à renvoyer un null');
        $this->assertNotEmpty($resultat[1], 'Second fichier n\'a rien renvoyé');
    }

    #[Depends('testCreateGames')]
    public function testGamesCreated(): void {
        $inDataBase = $this->repo->findAll();

        $firstCodeRencInList = $inDataBase[0]->getCodeRenc();
        $lastCodeRencInList = $inDataBase[count($inDataBase)- 1]->getCodeRenc();

        $gameTest = $this->repo->findWithCodeRenc($firstCodeRencInList);
        $resultTest = $this->repo->findWithCodeRenc($lastCodeRencInList);

        $this->assertNotNull($inDataBase, 'La recherche de toute la table GAME renvoie un null');
        $this->assertNotEmpty($inDataBase, 'La table GAME est vide');

        $this->assertNotNull($gameTest,'Aucun match trouvé avec '.$firstCodeRencInList);
        dump($gameTest);

        $this->assertNotNull($resultTest,'Aucun résultat trouvé avec '.$lastCodeRencInList);
        dump($resultTest);
    }
}
