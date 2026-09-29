<?php

namespace App\Tests;

use App\Repository\TeamRepository;
use App\Service\FindTeam;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TeamTest extends KernelTestCase
{

    private FindTeam $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = new FindTeam(
            static::getContainer()->get(TeamRepository::class),
            static::getContainer()->get(EntityManagerInterface::class)
        );
    }

    public function testFindTeamFromCompetition(): void
    {
        $team = $this->service->getTeamName('u12m-44', 'abc');
        $this->assertEquals('U12 Masculins', $team,);
    }

    public function testFindTeamFromPoule(): void
    {
        $team = $this->service->getTeamName('u15f-44', 'U15F D2');
        $this->assertEquals('U15 Féminins - Honneur A', $team,);
    }

    public function testCreateUnknownYouthTeam(): void
    {
        $this->assertEquals('U18 Masculins', $this->service->getTeamName('u18m-44', 'U18M D1'));
        $this->assertEquals('U17 Féminins', $this->service->getTeamName('u17f-44', 'U17F D1'));
        //Deuxième appel sur la même compétition : pas de doublon
        $this->assertSame(
            $this->service->getTeamName('u18m-44', 'U18M D1'),
            $this->service->getTeamName('u18m-44', 'U18M D2')
        );
    }

    public function testUnknownTeamNotYouth(): void
    {
        $this->expectException(Exception::class);
        $this->service->getTeamName('4dtm-44', 'abc');
    }
}
