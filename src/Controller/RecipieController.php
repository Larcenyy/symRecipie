<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Form\IngredientType;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class RecipieController extends AbstractController
{

    /*
    This controller display all recipies
    */


    #[Route('/recette', name: 'recipe.index', methods: "GET")]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {

        $recipe = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1), 10
        );

        return $this->render('pages/recipie/index.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    #[Route('/recette/creation', name: 'recipe.new', methods: ["GET", "POST"])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();


            $this->addFlash(
                'success',
                "Votre recette à était créer avec succès"
            );

            return $this->redirectToRoute('recipe.index');
        }
        else{
            $this->addFlash(
                'warning',
                "Votre recette à eu un problème"
            );
        }

        return $this->render("pages/recipie/new.html.twig",
        [
            "form" => $form->createView()
        ]);
    }

    #[Route('/recette/edit/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(
        Recipe $recipe,
        EntityManagerInterface $manager,
        Request $request
    ): Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();
            $manager->persist($recipe); // On soumet une demande
            $manager->flush(); // On effectue la demande

            $this->addFlash('success', 'Votre recette à était modifié avec succès !');

            $this->redirectToRoute("recipe.index");
        }

        return $this->render('pages/recipie/edit.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    #[Route('/recette/delete/{id}', name: 'recipe.delete', methods: ['GET'])]
    public function delete(
        Recipe $recipe,
        EntityManagerInterface $manager): Response
    {
        $manager->remove($recipe);
        $manager->flush();
        $this->addFlash('success', 'Votre recette à était supprimer avec succès !');

        return $this->redirectToRoute('recipe.index');
    }
}
