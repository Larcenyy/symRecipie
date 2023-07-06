<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'ingredient.index')]
    public function index(IngredientRepository $repo, PaginatorInterface $paginator,
    Request $request): Response
    {

        $ingredients = $paginator->paginate(
            $repo->findAll(),
            $request->query->getInt('page', 1), 10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }

    /*
        This controller show a form for create ingredient
    */

    #[Route('/ingredient/nouveau', 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            $manager->persist($ingredient);

            $this->addFlash(
                'success', 'Votre ingrédient à était créer avec succès !');


            $manager->flush();
            $this->redirect("/ingredient");
        }
        else{
            $this->addFlash(
                'warning', 'Un problème est survenu..');
        }

        return $this->render("pages/ingredient/new.html.twig", [
            'form' => $form->createView()
        ]);
    }

    #[Route('/ingredient/edit/{id}', 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(
        Ingredient $ingredient,
        EntityManagerInterface $manager,
        Request $request
    ): Response
    {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $ingredient = $form->getData();
            $manager->persist($ingredient); // On soumet une demande
            $manager->flush(); // On effectue la demande

            $this->addFlash('success', 'Votre ingrédient à était modifié avec succès !');

            $this->redirectToRoute("ingredient.index");
        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    #[Route('/ingredient/delete/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(
        Ingredient $ingredient,
        EntityManagerInterface $manager): Response
    {
        $manager->remove($ingredient);
        $manager->flush();
        $this->addFlash('success', 'Votre ingrédient à était supprimer avec succès !');

        return $this->redirectToRoute('ingredient.index');
    }

}
