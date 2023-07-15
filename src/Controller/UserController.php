<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    /**
     *  This controller  allow us to edit user profils
     * @param User $user
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/user/edit/{id}', name: 'user.edit', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function edit(User $user, Request $request, EntityManagerInterface $manager
    , UserPasswordHasherInterface $hasher): Response

    {
        if(!$this->getUser()){
            return $this->redirectToRoute('security.login');
        }

        if ($this->getUser() !== $user){
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

            if ($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())){
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Vos informations ont était modifié avec succès');
                return $this->redirectToRoute("recipe.index");
            }
            else{
                $this->addFlash('warning', 'Le mot de passe est incorrect');
            }
        }


        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/editpassword/{id}', name: 'user.edit.password', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER')]
    public function editPassword(User $user, Request $request,
    UserPasswordHasherInterface $hasher, EntityManagerInterface $manager
    ) : Response{

        if ($this->getUser() !== $user) {
            throw new AccessDeniedException("Vous n'avez pas la permission d'éditer ce profil.");
        }

        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])){

                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword']
                    )
                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Le mot de passe a était modifié');
                return $this->redirectToRoute("recipe.index");
            }
            else{
                $this->addFlash('warning', 'Mot de passe incorrect');
            }
        }


        return $this->render('pages/user/edit_password.html.twig', [
            "form" => $form->createView()
        ]);
    }
}
