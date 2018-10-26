<?php

namespace App\DataFixtures;

use App\Entity\League;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        
        $leagueRepository = $manager->getRepository('App:League');
        
        // Create 5 users
        for ($i = 0; $i < 5; $i++) {

            $user = new User();
            $encodedPassword = $this->passwordEncoder->encodePassword($user, 'pass' . $i);
            
            $user
                ->setEmail('user' . $i . '@localdev')
                ->setPassword($encodedPassword);
            
            $manager->persist($user);
        }
        
        $manager->flush();
        
        
        // Create 3 football leagues
        $leagueNames = [
            'Premier League',
            'EFL Championship',
            'EFL League One',
            'EFL League Two (empty)',
        ];
        
        foreach ($leagueNames as $leagueName) {

            $league = League::create($leagueName);
            $manager->persist($league);
        }
        
        $manager->flush();
        
        
        // Create 3 football teams for each league
        $teamsData = [
            [
                'name' => 'Manchester United',
                'strip' => 'red/black',
                'league' => $leagueNames[0]
            ],
            [
                'name' => 'Arsenal',
                'strip' => 'red/white',
                'league' => $leagueNames[0]
            ],
            [
                'name' => 'Chelsea',
                'strip' => 'blue',
                'league' => $leagueNames[0]
            ],
            [
                'name' => 'Aston Villa',
                'strip' => 'bordeaux/white',
                'league' => $leagueNames[1]
            ],
            [
                'name' => 'Birmingham City',
                'strip' => 'blue/white',
                'league' => $leagueNames[1]
            ],
            [
                'name' => 'Wigan Athletic',
                'strip' => 'white/blue',
                'league' => $leagueNames[1]
            ],
            [
                'name' => 'Accrington Stanley',
                'strip' => 'red',
                'league' => $leagueNames[2]
            ],
            [
                'name' => 'Luton Town',
                'strip' => 'orange',
                'league' => $leagueNames[2]
            ],
            [
                'name' => 'Shrewsbury Town',
                'strip' => 'blue/yellow',
                'league' => $leagueNames[2]
            ]
        ];
        
        foreach ($teamsData as $teamsDatum) {

            $league = $leagueRepository->findByName($teamsDatum['league']);
            $team = Team::create($teamsDatum['name'], $teamsDatum['strip'], $league);
            
            $manager->persist($team);
        }

        $manager->flush();
    }
}