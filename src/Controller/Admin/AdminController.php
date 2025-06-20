<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    #[Route('/formations', name: 'formations')]
    public function formations(): Response
    {
        return $this->render('admin/adminFormations.html.twig');
    }

    #[Route('/historique', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('admin/adminOrders.html.twig');
    }
}
