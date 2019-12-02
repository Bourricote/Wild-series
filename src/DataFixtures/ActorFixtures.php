<?php


namespace App\DataFixtures;


use App\Entity\Actor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

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

        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 50; $i++) {
            $actor = new Actor();
            $actor->setName($faker->name);
            $number = rand(0, 2);
            $actor->addProgram($this->getReference('program_' . $number));
            $manager->persist($actor);
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
