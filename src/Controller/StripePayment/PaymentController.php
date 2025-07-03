<?php

namespace App\Controller\StripePayment;

use App\Repository\CartItemRepository;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;

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
    public function checkout(
        Request $request,
        CartItemRepository $cartItemRepository,
        StripeService $stripeService,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $token = new CsrfToken('checkout', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

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
        return $this->render('payment/cancel.html.twig');
    }
}