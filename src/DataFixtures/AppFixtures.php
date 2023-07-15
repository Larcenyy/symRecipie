<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Mark;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /*
     * @var Generator
     */
    private Generator $faker;
    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {

        //Users
        $users = [];
        for ($i = 0; $i <= 10; $i++){
            $user = new User();
            $user->setFullName($this->faker->name())
                ->setPseudo(mt_rand(0, 1) === 1 ? $this->faker->firstName() : null)
                ->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPlainPassword('password');

            $users[] = $user;
            $manager->persist($user);
        }

        // Ingrédient
        $ingredients = [];
        for ($i = 0; $i <= 50; $i++){
            $ingredient = new Ingredient();
            $ingredient->setName($this->faker->word())
                ->setPrice(mt_rand(0, 100))
                ->setIngredient($users[mt_rand(0, count($users) - 1)]);
            $ingredients[] = $ingredient;
            $manager->persist($ingredient);
        }

        // Recette
        $recipes = [];
        for ($k = 0; $k < 25; $k++) {
            $recipe = new Recipe();
            $recipe->setName($this->faker->word())
                ->setTime(mt_rand(0, 1) == 1 ? mt_rand(1, 1440) : null)
                ->setNbPeople(mt_rand(0, 1) == 1 ? mt_rand(1, 50) : null)
                ->setDifficulty(mt_rand(0, 1) == 1 ? mt_rand(1, 5) : null)
                ->setDescription($this->faker->text(300))
                ->setPrice(mt_rand(0, 1) == 1 ? mt_rand(1, 1000) : null)
                ->setIsFavorite(mt_rand(0, 1) == 1 ? true : false)
                ->setIsPublic(mt_rand(0, 1) == 1 ? true : false)
                ->setUser($users[mt_rand(0, count($users) - 1)]);
            ;
            for ($j = 0; $j < 25; $j++) {
                $recipe->addIngredient($ingredients[mt_rand(0, count($ingredients) - 1 )]);
            }

            $recipes[] = $recipe;

            $manager->persist($recipe);
        }


        // Système de note
        foreach ($recipes as $recipe){
            for ($i = 0; $i < mt_rand(0, 4); $i++){
                $mark = new Mark();

                $mark
                ->setMark(mt_rand(1, 5))
                ->setUser($users[mt_rand(0, count($users) -1 )])
                ->setRecipe($recipe);

                $manager->persist($mark);
            }
        }

        $manager->flush();
    }
}
