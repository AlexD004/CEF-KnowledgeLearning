<?php

namespace App\Service\User;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Service responsible for handling user registration logic:
 * - Password hashing
 * - Assigning roles and metadata
 * - Persisting to the database
 * - Sending confirmation email
 */
class UserRegistrationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security,
        private EmailVerifier $emailVerifier
    ) {}

    /**
     * Registers a new user in the system.
     *
     * @param User $user The user to register
     * @param string $plainPassword The raw password provided by the user
     */
    public function register(User $user, string $plainPassword): void
    {
        // 1. Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // 2. Assign default role
        $user->setRoles(['ROLE_CLIENT']);

        // 3. Set createdBy if someone is logged in (e.g., admin)
        $creator = $this->security->getUser();
        if ($creator instanceof User) {
            $user->setCreatedBy($creator);
        }

        // 4. Persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // 5. Send confirmation email
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@knowledge.local', 'Knowledge Learning'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('emails/confirmation_email.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user, $email);
    }
}
