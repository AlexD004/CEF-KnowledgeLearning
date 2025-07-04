<?php

namespace App\Controller\StripePayment;

use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller handling Stripe webhook events after payment completion.
 *
 * Listens for 'checkout.session.completed' to process successful orders.
 */
class StripeWebhookController extends AbstractController
{
    /**
     * Handles the Stripe webhook event when a checkout session is completed.
     *
     * Delegates the actual business logic to the PurchaseService.
     * This method should be protected from unauthorized access (Stripe signs webhook calls).
     *
     * Expected JSON payload format (simplified):
     * {
     *   "type": "checkout.session.completed",
     *   "data": {
     *     "object": {
     *       "id": "cs_test_...",
     *       "customer_email": "user@example.com"
     *     }
     *   }
     * }
     *
     * @param Request $request The incoming HTTP request containing the Stripe payload
     * @param PurchaseService $purchaseService The service responsible for handling post-payment operations
     * @return Response A 200 response on success, 4xx on failure or ignored events
     */
    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(
        Request $request,
        PurchaseService $purchaseService
    ): Response {
        // Retrieve and decode the raw JSON payload from Stripe
        $payload = $request->getContent();
        $data = json_decode($payload, true);

        // Validate event type
        if (!isset($data['type']) || $data['type'] !== 'checkout.session.completed') {
            return new Response('Ignored or malformed event', 400);
        }

        // Extract the session object from the event
        $session = $data['data']['object'] ?? null;
        if (!$session) {
            return new Response('Missing session object', 400);
        }

        // Delegate payment processing to the dedicated service
        $purchaseService->processSuccessfulPayment($session);

        return new Response('Webhook processed', 200);
    }
}
