<?php

namespace App\Tests;

use App\Repository\TeamRepository;
use App\Service\FindTeam;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TeamTest extends KernelTestCase
{

    private FindTeam $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = new FindTeam(static::getContainer()->get(TeamRepository::class));
    }

    public function testFindTeamFromCompetition(): void
    {
        $team = $this->service->getTeamsName('u12m-44', 'abc');
        $this->assertEquals('U12 Masculins', $team,);
    }

    public function testFindTeamFromPoule(): void
    {
        $team = $this->service->getTeamsName('u15f-44', 'U15F D2');
        $this->assertEquals('U15 Féminins - Honneur A', $team,);
    }
}
