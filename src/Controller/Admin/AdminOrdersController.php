<?php

namespace App\Controller\Admin;

use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Admin controller to list all orders.
 */
class AdminOrdersController extends AbstractController
{
    /**
     * Displays all orders placed on the platform, grouped by user.
     *
     * @param OrdersRepository $ordersRepo
     * @return Response
     */
    #[Route('/admin/historique', name: 'admin_orders')]
    public function listAllOrders(OrdersRepository $ordersRepo): Response
    {
        // Fetch all orders with their user and items
        $orders = $ordersRepo->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/adminOrders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
