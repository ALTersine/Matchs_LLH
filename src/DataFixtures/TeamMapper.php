<?php

namespace App\DataFixtures;

use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TeamMapper extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $equipes = [
            1=>'Loisirs Mixte',

            2=>'Seniors Féminins',
            3=>'Seniors Masculins',
            4=>'Seniors Masculins 2',

            5=>'U19 Masculins',

            6=>'U17 Féminins',

            7=>'U16 Masculins',

            8=>'U15 Féminins - Honneur A',
            9=>'U15 Féminins - Honneur B',

            10=>'U14 Masculin - Honneur A',
            11=>'U14 Masculin - Honneur B',

            12=>'U13 Féminins',

            13=>'U12 Masculins',

            14=>'U11 Masculins',
            15=>'U11 Féminins',

            16=>'U10 Féminin',
            17=>'U10 Masculins',

            18=>'Ecole de hand',
            19=>'Baby Hand'
        ];

        $manager->persist(new Team('d2fpl ; division 2 feminine territoriale', $equipes[2]));
        $manager->persist(new Team('2dtm-44', $equipes[3]));
        $manager->persist(new Team('3dtm-44', $equipes[4]));
        $manager->persist(new Team('u19m-44', $equipes[5]));
        $manager->persist(new Team('', $equipes[6]));
        $manager->persist(new Team('u16m-44', $equipes[7]));
        $manager->persist(new Team('u13f-44', $equipes[12]));
        $manager->persist(new Team('u12m-44', $equipes[13]));
        $manager->persist(new Team('u11m-44', $equipes[14]));
        $manager->persist(new Team('', $equipes[15]));
        $manager->persist(new Team('u10f-44', $equipes[16]));
        $manager->persist(new Team('u10m-44', $equipes[17]));
        $manager->persist(new Team('ecole de hand mixte - 44', $equipes[18]));
        $manager->persist(new Team('', $equipes[19]));
        
        
        $u14a = new Team('u14m-44', $equipes[10]);
        $u14a->setCodePoule('U14M D7');
        $manager->persist($u14a);

        $u14b = new Team('u14m-44', $equipes[11]);
        $u14b->setCodePoule('U14M D13');
        $manager->persist($u14b);

        $u15a = new Team('u15f-44', $equipes[8]);
        $u15a->setCodePoule('U15F D2');
        $manager->persist($u15a);

        $u15b = new Team('u15f-44', $equipes[9]);
        $u15b->setCodePoule('U15F D8');
        $manager->persist($u15b);

        $manager->flush();
    }
}
