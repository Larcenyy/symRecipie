<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    #[Route('/connexion', name: 'security.login', methods: ["POST", "GET"])]
    public function index(AuthenticationUtils $authenticationUtils ): Response
    {
        return $this->render('pages/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * @return Response
     */
    #[Route('/logout', name: 'security.logout', methods: ["POST", "GET"])]
    public function logout(): Response
    {
        // Automatique
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/inscription', name: 'security.register', methods: ["POST", "GET"])]
    public function registration(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher) : Response{

        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $userInfo = $form->getData();

            $plainPass = $user->getPlainPassword();

            $hashed = $passwordHasher->hashPassword(
                $user,
                $plainPass
            );

            $user->setPassword($hashed);

            $manager->persist($userInfo);
            $manager->flush();

            $this->addFlash("success", "Vous êtes désormais enregistrer !");

            return $this->redirectToRoute("security.login");
        }

        return $this->render('pages/security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
