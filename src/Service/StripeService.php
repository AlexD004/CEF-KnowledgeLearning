<?php

namespace App\Service;

use App\Entity\CartItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    private string $stripeKey;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(string $stripeKey, UrlGeneratorInterface $urlGenerator)
    {
        $this->stripeKey = $stripeKey;
        $this->urlGenerator = $urlGenerator;
        Stripe::setApiKey($stripeKey);
    }

    /**
     * Create a Stripe Checkout session based on cart items.
     *
     * @param CartItem[] $cartItems
     * @return string The URL to redirect the user to Stripe
     */
    public function createCheckoutSession(array $cartItems): string
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            $label = $item->getLesson()?->getName() ?? $item->getCursus()?->getName();
            $priceTTC = $item->getLesson()?->getPrice() ?? $item->getCursus()?->getPrice();

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $label,
                    ],
                    'unit_amount' => (int) ($priceTTC * 100), // Stripe attend des centimes
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $session->url;
    }
}
