<?php

namespace App\Controller;

use App\Entity\Mark;
use App\Entity\Recipe;
use App\Form\MarkType;
use App\Form\RecipeType;
use App\Repository\MarkRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RecipieController extends AbstractController
{
    /*
    This controller display all recipies
    */
    #[Route('/recette', name: 'recipe.index', methods: "GET")]
    #[IsGranted('ROLE_USER')]
    public function index(RecipeRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {

        $recipe = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1), 10
        );

        return $this->render('pages/recipie/index.html.twig', [
            'recipe' => $recipe,
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/recette/publique', name: 'recipe.public', methods: "GET")]
    public function indexPublic(RecipeRepository $repository, PaginatorInterface $paginator, Request $request) : Response{

        $recipes = $paginator->paginate(
            $repository->findPublicRecipe(null),
            $request->query->getInt('page', 1), 6
        );

        return $this->render('pages/recipie/index_public.html.twig', [
            'recipe' => $recipes
        ]);
    }

    /**
     * @param Recipe $recipe
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/{id}', name: 'recipe.show', methods: ["GET", "POST"])]
    public function show(Recipe $recipe, Request $request, EntityManagerInterface $manager, MarkRepository $markRepository) : Response{

        if (!$recipe->getIsPublic()) {
            throw new AccessDeniedException("Cette recette n'est pas rendu public.");
        }

        $mark = new Mark();

        $form = $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $mark->setUser($this->getUser())
            ->setRecipe($recipe);

            $existMark = $markRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe
            ]);

            if (!$existMark){
                $manager->persist($mark);
                $this->addFlash('success', 'Vous avez noter cette recette avec succès.');
            }

            else{
                $existMark->setMark($form->getData()->getMark());
                $this->addFlash('success', 'Vous avez changer votre note avec succès.');
            }

                $manager->flush();


                return $this->redirectToRoute('recipe.show', ['id' => $recipe->getId()]);
            }

        return $this->render('pages/recipie/show.html.twig',
        [
            'recipe' => $recipe,
            'form' => $form->createView()
        ]);
    }

    #[Route('/recette/creation', name: 'recipe.new', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());

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

    /**
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return Response
     */
    #[Route('/recette/edit/{id}', 'recipe.edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Recipe $recipe,
        EntityManagerInterface $manager,
        Request $request
    ): Response
    {

        if ($this->getUser() !== $recipe->getUser()) {
            throw new AccessDeniedException("Vous n'avez pas la permission d'éditer cette recettes.");
        }

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

    /**
     * @param Recipe $recipe
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/recette/delete/{id}', name: 'recipe.delete', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Recipe $recipe,
        EntityManagerInterface $manager): Response
    {
        if ($this->getUser() !== $recipe->getUser()) {
            throw new AccessDeniedException("Vous n'avez pas la permission de supprimer cette recettes.");
        }

        $manager->remove($recipe);
        $manager->flush();
        $this->addFlash('success', 'Votre recette à était supprimer avec succès !');

        return $this->redirectToRoute('recipe.index');
    }
}
