<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserCertification;
use Mpdf\Mpdf;

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

    /**
     * Generates a downloadable PDF certification for the authenticated user.
     *
     * This route is restricted to the user who owns the certification.
     * The generated PDF includes user's name, cursus name, and date of completion.
     *
     * @param UserCertification $certification The certification entity (injected from route)
     * @return Response A PDF file rendered inline in the browser
     */
    #[Route('/apprenant/certificats/{id}/pdf', name: 'client_certificates_pdf')]
    public function generateCertificatePdf(UserCertification $certification): Response
    {
        $user = $this->getUser();

        // Security check: ensure the logged-in user owns this certification
        if ($certification->getUser() !== $user) {
            throw $this->createAccessDeniedException("Ce certificat ne vous appartient pas.");
        }

        // Render HTML template for the PDF
        $html = $this->renderView('client/certificate_pdf.html.twig', [
            'user' => $user,
            'certification' => $certification,
        ]);

        // Configure mPDF
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => __DIR__ . '/../../var/tmp', 
        ]);

        $mpdf->WriteHTML($html); 

        // Generate a filename
        /** @var \App\Entity\Cursus $cursus */
        $cursus = $certification->getCursus();
        /** @var \App\Entity\User $owner */
        $user = $certification->getUser();

        $filename = 'certificat_' . $cursus->getId() . '_' . $user->getId() . '.pdf';

        // Return the PDF as an inline response
        return new Response(
            $mpdf->Output($filename, 'I'), // 'I' for inline display in browser
            200,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
