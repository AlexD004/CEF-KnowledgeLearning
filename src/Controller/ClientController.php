<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/apprenant', name: 'client_')]
class ClientController extends AbstractController
{
    #[Route('/informations', name: 'informations')]
    public function informations(): Response
    {
        return $this->render('client/userInfos.html.twig');
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
