<?php

namespace App\DataFixtures;

use App\Domain\League\League;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $league = new League('Premier League', 'premier-league');
        $league->setApiId('123');
        $league->setLogo('premier-league-logo.png');
        $manager->persist($league);


        $manager->flush();
    }
}
