<?php


namespace App\DataFixtures;


use App\Entity\Actor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{

    const ACTORS = [
        'Bryan Cranston' => [
            'programs' => ['program_0', 'program_1']
        ],
        'Frankie Muniz' => [
            'programs' => ['program_0',]
        ],
        'Jane Kaczmarek' => [
            'programs' => ['program_0',]
        ],
        'Andrew Lincoln' => [
            'programs' => ['program_1',]
        ],
    ];

    /**
     * Load data fixtures with the passed EntityManager
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::ACTORS as $name => $data) {
            $actor = new Actor();
            $actor->setName($name);
            foreach ($data['programs'] as $program) {
                $actor->addProgram($this->getReference($program));
            }

            $manager->persist($actor);
            $this->addReference('actor_' . $name, $actor);
        }
        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }
}