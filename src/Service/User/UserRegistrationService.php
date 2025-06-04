<?php

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class UserRegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security
    ) {}

    /**
     * Register a new user in the system.
     *
     * @param User $user The user to register
     * @param string $plainPassword The plain password from the form
     * @return void
     */
    public function register(User $user, string $plainPassword): void
    {
        // 1. Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // 2. Assign default role
        $user->setRoles(['ROLE_CLIENT']);

        // 3. Set createdBy if an admin is logged in
        $creator = $this->security->getUser();
        if ($creator instanceof User) {
            $user->setCreatedBy($creator);
        }

        // 4. Persist and flush
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
