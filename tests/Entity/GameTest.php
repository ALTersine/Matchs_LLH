<?php

namespace App\Tests;

use App\Entity\Game;
use App\Repository\GameRepository;
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
    private const string CODE4 = "ABCDEFG";

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
        $game1rec = "TRIGNAC HANDBALL 2";
        $game1->setCodeRenc(self::CODE1);
        $game1->setCompetition("2dtm-44");
        $game1->setPoule("2DTM - POULE HAUTE B");
        $game1->setDate("07/03/2026");
        $game1->setHeure("21:00:00");
        $game1->setClubADomicile($game1rec);
        $game1->setHosting($game1rec);
        $game1->setClubExterieur("LE LANDREAU HANDBALL 1");
        $game1->setScoreADomicile(25);
        $game1->setScoreExterieur(29);
        $game1->setEtat("JOUE");

        $this->em->persist($game1);

        $game2 = new Game();
        $game2rec = "LE LANDREAU HANDBALL 2";
        $game2->setCodeRenc(self::CODE2);
        $game2->setCompetition("3dtm-44");
        $game2->setPoule("3DTM - POULE BASSE A");
        $game2->setDate("07/03/2026");
        $game2->setHeure("21:00:00");
        $game2->setClubADomicile($game2rec);
        $game2->setClubExterieur("ST NAZAIRE HANDBALL 4");
        $game2->setScoreADomicile(33);
        $game2->setScoreExterieur(35);
        $game2->setHosting($game2rec);
        $game2->setEtat("JOUE");

        $this->em->persist($game2);

        $game3 = new Game();
        $game3rec = "MONTAIGU VHB U21";
        $game3->setCodeRenc(self::CODE3);
        $game3->setCompetition("d2fpl ; division 2 feminine territoriale");
        $game3->setPoule("D2FPL-B ; POULE B");
        $game3->setDate("15/03/2026");
        $game3->setHeure("16:30:00");
        $game3->setClubADomicile($game3rec);
        $game3->setHosting($game3rec);
        $game3->setClubExterieur("LE LANDREAU U21");

        $this->em->persist($game3);

        $game4 = new Game();
        $game4rec = "TRIGNAC HANDBALL 2";
        $game4->setCodeRenc(self::CODE4);
        $game4->setCompetition("2dtm-44");
        $game4->setPoule("2DTM - POULE HAUTE B");
        $game4->setDate("07/03/2026");
        $game4->setHeure("21:00:00");
        $game4->setClubADomicile($game4rec);
        $game4->setHosting($game4rec);
        $game4->setClubExterieur("LE LANDREAU HANDBALL 1");
        $game4->setScoreADomicile(00);
        $game4->setScoreExterieur(10);
        $game4->setEtat("JOUE");
        $game4->setForfait("Visiteur");

        $this->em->persist($game4);

        $this->em->flush();

        //Validation de l'intégration des tests
        $this->assertNotNull($this->gettingTesteur(self::CODE1), "Erreur sur le match1");
        $this->assertNotNull($this->gettingTesteur(self::CODE2), "Erreur sur le match2");
        $this->assertNotNull($this->gettingTesteur(self::CODE3), "Erreur sur le match3");
        $this->assertNotNull($this->gettingTesteur(self::CODE4), "Erreur sur le match4");
    }

    #[Depends('testCreateGame')]
    public function testDomicile(): void
    {
        $this->assertFalse($this->gettingTesteur(self::CODE1)->isHosting());
        $this->assertTrue($this->gettingTesteur(self::CODE2)->isHosting());
        $this->assertFalse($this->gettingTesteur(self::CODE3)->isHosting());
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
                $this->gettingTesteur(self::CODE1)->winner(),
                'LANDREAU'
            )
        );

        $this->assertFalse(
            str_contains(
                $this->gettingTesteur(self::CODE2)->winner(),
                'LANDREAU'
            )
        );

        $this->assertNotNull($this->gettingTesteur(self::CODE4)->getForfait());
    }
}
