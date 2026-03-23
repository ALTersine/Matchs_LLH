<?php

namespace App\Tests;

use App\Entity\Game;
use App\Repository\GameRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameTest extends KernelTestCase
{
    private GameRepository $repo;
    private EntityManagerInterface $em;

    private const string CODE1 = "VAGMBDD";
    private const string CODE2 = "VAGMBHU";
    private const string CODE3 = "VAGENBE";

    private function gettingTesteur(string $code): Game | null
    {
        self::bootKernel();
        $this->repo = static::getContainer()->get(GameRepository::class);

        return $this->repo->findOneBy(["codeRenc" => $code]);
    }

    public function testCreateGame(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repo = static::getContainer()->get(GameRepository::class);

        //Retirer ce qu'il y a en base avant
        $existingTests = $this->repo->findAll();
        if(count($existingTests) > 0){
            foreach($existingTests as $test){
                $this->em->remove($test);
            }
        }

        //Création de mes matchs 1 à domicile, l'autre à l'extérieur, et un pour le weekend prochain
        $game1 = new Game();
        $game1->setCodeRenc(self::CODE1);
        $game1->setCompetition("2dtm-44");
        $game1->setPoule("2DTM - POULE HAUTE B");
        $game1->setDate("07/03/2026");
        $game1->setHeure("21:00:00");
        $game1->setClubADomicile("TRIGNAC HANDBALL 2");
        $game1->setClubExterieur("LE LANDREAU HANDBALL 1");
        $game1->setScoreADomicile(25);
        $game1->setScoreExterieur(29);
        $game1->setEtat("JOUE");

        $this->em->persist($game1);

        $game2 = new Game();
        $game2->setCodeRenc(self::CODE2);
        $game2->setCompetition("3dtm-44");
        $game2->setPoule("3DTM - POULE BASSE A");
        $game2->setDate("07/03/2026");
        $game2->setHeure("21:00:00");
        $game2->setClubADomicile("LE LANDREAU HANDBALL 2");
        $game2->setClubExterieur("ST NAZAIRE HANDBALL 4");
        $game2->setScoreADomicile(33);
        $game2->setScoreExterieur(30);
        $game2->setEtat("JOUE");

        $this->em->persist($game2);

        $game3 = new Game();
        $game3->setCodeRenc(self::CODE3);
        $game3->setCompetition("d2fpl ; division 2 feminine territoriale");
        $game3->setPoule("D2FPL-B ; POULE B");
        $game3->setDate("15/03/2026");
        $game3->setHeure("16:30:00");
        $game3->setClubADomicile("MONTAIGU VHB U21");
        $game3->setClubExterieur("LE LANDREAU U21");

        $this->em->persist($game3);

        $this->em->flush();

        //Validation de l'intégration des tests
        $this->assertNotNull($this->gettingTesteur(self::CODE1), "Erreur sur le match1");
        $this->assertNotNull($this->gettingTesteur(self::CODE2), "Erreur sur le match2");
        $this->assertNotNull($this->gettingTesteur(self::CODE3), "Erreur sur le match3");
    }

    #[Depends('testCreateGame')]
    public function testDomicile(): void
    {
        $this->assertFalse($this->gettingTesteur(self::CODE1)->isADomicile());
        $this->assertTrue($this->gettingTesteur(self::CODE2)->isADomicile());
        $this->assertFalse($this->gettingTesteur(self::CODE3)->isADomicile());
    }

    #[Depends('testCreateGame')]
    public function testGamePreviewOrResultat(): void
    {
        $this->assertFalse($this->gettingTesteur(self::CODE1)->isGamePreview());
        $this->assertFalse($this->gettingTesteur(self::CODE2)->isGamePreview());
        $this->assertTrue($this->gettingTesteur(self::CODE3)->isGamePreview());
    }

    #[Depends('testCreateGame')]
    public function testWichWeekDayIsTheGame(): void
    {
        $this->assertEquals('saturday', $this->gettingTesteur(self::CODE1)->gameWeekDay());
        $this->assertEquals('saturday', $this->gettingTesteur(self::CODE2)->gameWeekDay());
        $this->assertEquals('sunday', $this->gettingTesteur(self::CODE3)->gameWeekDay());
    }

    #[Depends('testCreateGame')]
    public function testWinner(): void
    {
        $this->assertTrue(
            str_contains(
                $this->gettingTesteur(self::CODE1)->getClubExterieur(),
                'LANDREAU'
            )
        );

        $this->assertTrue(
            str_contains(
                $this->gettingTesteur(self::CODE2)->getClubADomicile(),
                'LANDREAU'
            )
        );
    }
}
