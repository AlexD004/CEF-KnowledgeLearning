<?php

namespace App\Controller\Client;

use App\Repository\CartItemRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles payment redirection and results.
 */
class PaymentController extends AbstractController
{
    /**
     * Redirects to Stripe Checkout session for the current user's cart.
     *
     * @param CartItemRepository $cartItemRepository
     * @param StripeService $stripeService
     * @return Response
     */
    #[Route('/paiement', name: 'payment_checkout')]
    public function checkout(CartItemRepository $cartItemRepository, StripeService $stripeService): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cartItems = $cartItemRepository->findBy(['user' => $user]);

        if (!$cartItems) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('cart_show');
        }

        $checkoutUrl = $stripeService->createCheckoutSession($cartItems);
        return $this->redirect($checkoutUrl);
    }

    /**
     * Page shown after successful payment.
     *
     * @return Response
     */
    #[Route('/paiement/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    /**
     * Page shown when payment is cancelled.
     *
     * @return Response
     */
    #[Route('/paiement/annulation', name: 'payment_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé.');
        return $this->redirectToRoute('cart_show');
    }
}