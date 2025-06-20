<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\UserType;
use App\Entity\User;

#[Route('/apprenant', name: 'client_')]
class ClientInfosController extends AbstractController
{
    /**
     * Allows the authenticated client to view and edit their personal information.
     * 
     * Route: /apprenant/informations
     * Name: client_informations
     */
    #[Route('/informations', name: 'informations')]
    public function informations(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $em->flush();

            $this->addFlash('success', 'Informations mises à jour avec succès.');
            return $this->redirectToRoute('client_informations');
        }

        return $this->render('client/userInfos.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/formations', name: 'formations')]
    public function formations(): Response
    {
        return $this->render('client/userFormations.html.twig');
    }

    #[Route('/certificats', name: 'certificates')]
    public function certificates(): Response
    {
        return $this->render('client/userCertificates.html.twig');
    }

    #[Route('/achats', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('client/userOrders.html.twig');
    }
}
