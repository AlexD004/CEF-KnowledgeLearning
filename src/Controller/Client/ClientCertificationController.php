<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserCertification;

/**
 * Controller handling the display of user certifications (validated cursus).
 */
class ClientCertificationController extends AbstractController
{
    /**
     * Displays the list of cursus the current user has validated (certifications).
     *
     * Route: /apprenant/certificats
     *
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @return Response Rendered HTML view with certifications
     */
    #[Route('/apprenant/certificats', name: 'client_certificates')]
    public function certificates(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Retrieve all certifications for this user
        $certifications = $em->getRepository(UserCertification::class)->findBy([
            'user' => $user,
        ]);

        return $this->render('client/userCertificates.html.twig', [
            'certifications' => $certifications,
        ]);
    }
}
