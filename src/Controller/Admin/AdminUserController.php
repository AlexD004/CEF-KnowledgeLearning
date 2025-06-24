<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller for managing users via the admin interface.
 * Provides list, create, edit, and delete functionalities.
 */
class AdminUserController extends AbstractController
{
    /**
     * Displays a list of all users.
     *
     * @param UserRepository $userRepository The user repository
     *
     * @return Response The rendered user list page
     */
    #[Route('/admin/utilisateur', name: 'admin_user_list')]
    public function listUsers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/adminUsersList.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Displays and processes the form to create a new user.
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @param UserPasswordHasherInterface $passwordHasher For encoding passwords
     *
     * @return Response The rendered user creation form or redirect
     */
    #[Route('/admin/utilisateur/creation', name: 'admin_user_create')]
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $plainPassword = $form->get('plainPassword')->getData();

            if (!$plainPassword) {
                $form->get('plainPassword')->addError(
                    new \Symfony\Component\Form\FormError("Le mot de passe est requis à la création.")
                );
            }

            if ($form->isValid()) {
                $selectedRole = $form->get('roles')->getData();
                $user->setRoles([$selectedRole]);

                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

                $admin = $this->getUser();
                $user->setCreatedBy($admin);
                $user->setUpdatedBy($admin);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Nouvel utilisateur créé avec succès.');
                return $this->redirectToRoute('admin_user_list');
            }
        }

        return $this->render('admin/adminUsersAdd.html.twig', [
            'form' => $form->createView(),
            'user' => null,
        ]);
    }


    /**
     * Displays and processes the form to edit an existing user.
     *
     * @param User $user The user to edit (retrieved via ID from the route)
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @param UserPasswordHasherInterface $passwordHasher For encoding passwords
     *
     * @return Response The rendered user edit form or redirect
     */
    #[Route('/admin/utilisateur/{id}', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $user->setUpdatedAt(new \DateTimeImmutable());
            $user->setUpdatedBy($this->getUser());

            $em->flush();

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('admin_user_list');
        }

        return $this->render('admin/adminUsersAdd.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Deletes a user after validating the CSRF token.
     *
     * @param Request $request The current HTTP request
     * @param User $user The user to delete
     * @param EntityManagerInterface $em The Doctrine entity manager
     *
     * @return Response Redirects to the user list after deletion
     */
    #[Route('/admin/utilisateur/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted successfully.');
        }

        return $this->redirectToRoute('admin_user_list');
    }
}
