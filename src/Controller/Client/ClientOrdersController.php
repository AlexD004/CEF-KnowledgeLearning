<?php

namespace App\Controller\Client;

use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for displaying user orders in client area.
 */
#[IsGranted('ROLE_CLIENT')]
class ClientOrdersController extends AbstractController
{
    /**
     * Display the list of orders for the currently logged-in client.
     *
     * @param OrdersRepository $ordersRepo Repository to fetch orders
     * @return Response
     */
    #[Route('/apprenant/achats', name: 'client_orders')]
    public function orders(OrdersRepository $ordersRepo): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Fetch all orders for the current user, sorted by newest first
        $orders = $ordersRepo->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('client/userOrders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
