<?php


namespace App\DataFixtures;


use App\Entity\Program;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProgramFixtures extends Fixture  implements DependentFixtureInterface
{
    const PROGRAMS = [
        'Malcolm' => [
            'summary' => 'The series follows a dysfunctional working-class family and stars Frankie Muniz in the lead role of Malcolm, a somewhat normal boy who tests at genius level. While he enjoys his intelligence, he despises having to take classes for gifted children, who are mocked by the other students who call them "Krelboynes". Jane Kaczmarek is Malcolm\'s overbearing, authoritarian mother, Lois, and Bryan Cranston plays his immature but loving father, Hal. Christopher Kennedy Masterson plays eldest brother Francis, a former rebel who, in earlier episodes, was in military school, but eventually marries and settles into a steady job',
            'poster' => 'https://m.media-amazon.com/images/M/MV5BNTc2MzM2N2YtZDdiOS00M2I2LWFjOGItMDM3OTA3YjUwNjAxXkEyXkFqcGdeQXVyNzA5NjUyNjM@._V1_.jpg',
            'category' => 'category_2',
            ],
        'Walking Dead' => [
            'summary'=> 'Le policier Rick Grimes se réveille après un long coma. Il découvre avec effarement que le monde, ravagé par une épidémie, est envahi par les morts-vivants.',
            'poster' => 'https://m.media-amazon.com/images/M/MV5BZmFlMTA0MmUtNWVmOC00ZmE1LWFmMDYtZTJhYjJhNGVjYTU5XkEyXkFqcGdeQXVyMTAzMDM4MjM0._V1_.jpg',
            'category' => 'category_1',
            ],
        'Friends' => [
            'summary' => 'Une bande de potes à New York',
            'poster' => 'https://m.media-amazon.com/images/M/MV5BNDVkYjU0MzctMWRmZi00NTkxLTgwZWEtOWVhYjZlYjllYmU4XkEyXkFqcGdeQXVyNTA4NzY1MzY@._V1_.jpg',
            'category' => 'category_2',
        ]
        ];

    public function load(ObjectManager $manager)
    {
        $i = 0;
        $slugify = new Slugify();
        foreach (self::PROGRAMS as $title => $data) {
            $program = new Program();
            $program->setTitle($title);
            $program->setSummary($data['summary']);
            $program->setPoster($data['poster']);
            $program->setCategory($this->getReference($data['category']));
            $program->setSlug($slugify->generate($title));
            $manager->persist($program);
            $this->addReference('program_' . $i, $program);
            $i++;
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
        return [CategoryFixtures::class];
    }
}
