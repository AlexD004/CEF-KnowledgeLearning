<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller responsible for handling email verification from the signed URL.
 */
class VerifyEmailController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     *
     * Handles the email verification process when user clicks the link.
     *
     * @param Request $request The current HTTP request
     * @return Response Redirects to homepage with flash messages
     */
    #[Route('/verify/email', name: 'app_verify_email')]
    public function __invoke(Request $request): Response
    {
        $userId = $request->get('id');

        if (!$userId) {
            throw $this->createNotFoundException('No user ID provided in verification link.');
        }

        $user = $this->entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException('User not found.');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);

            // ✅ Mark as verified and persist
            $user->setIsVerified(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Your email has been verified.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Email verification failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('homepage');
    }

}
