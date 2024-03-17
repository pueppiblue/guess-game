<?php

namespace App\DataFixtures;

use App\Domain\League\League;
use App\Domain\Player\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $league = new League('Premier League', 'premier-league');
        $league->setApiId('123');
        $league->setLogo('premier-league-logo.png');
        $manager->persist($league);

        $user = new Player();
        $user->setUsername('admin');
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'admin'
        );
        $user->setPassword($hashedPassword);
        $user->setEmail('admin@guess-game.com');
        $manager->persist($user);

        $manager->flush();
    }
}
