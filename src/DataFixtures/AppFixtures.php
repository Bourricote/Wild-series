<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Category;
use App\Entity\Program;
use App\Service\Slugify;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');
        $slugify = new Slugify();

        for ($i = 1; $i <= 1000; $i++) {
            $category = new Category();
            $category->setName($faker->word);
            $manager->persist($category);
            $this->addReference('category_' . $i, $category);
        }

        for ($i = 1; $i <= 1000; $i++) {
            $actor = new Actor();
            $actor->setName($faker->firstName);
            $actor->setSlug($slugify->generate($actor->getName()));
            $
            $manager->persist($actor);
        }

        for ($i = 1; $i <= 1000; $i++) {
            $program = new Program();
            $program->setTitle($faker->sentence(4, true));
            $program->setSlug($slugify->generate($program->getTitle()));
            $program->setSummary($faker->text(100));
            $program->setPoster($faker->text(100));
            $program->setCategory($this->getReference('category_'.rand(1,1000)));
            $manager->persist($program);
        }

        $manager->flush();
    }
}
