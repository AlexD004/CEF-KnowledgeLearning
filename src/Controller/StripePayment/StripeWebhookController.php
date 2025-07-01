<?php

namespace App\Controller\StripePayment;

use App\Entity\Orders;
use App\Entity\OrderItem;
use App\Entity\UserLesson;
use App\Repository\UserRepository;
use App\Repository\LessonRepository;
use App\Repository\CursusRepository;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller handling Stripe webhook calls after checkout success.
 */
class StripeWebhookController extends AbstractController
{

    /**
     * Handles the Stripe webhook event when payment is completed.
     *
     * This endpoint listens to Stripe's 'checkout.session.completed' event
     * and processes the following:
     * - Registers the order and order items in DB
     * - Links lessons to the user (via UserLesson)
     * - Clears the user's cart
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepo
     * @param LessonRepository $lessonRepo
     * @param CursusRepository $cursusRepo
     * @param CartItemRepository $cartRepo
     * @return Response
     */
    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handleWebhook(
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        LessonRepository $lessonRepo,
        CursusRepository $cursusRepo,
        CartItemRepository $cartRepo
    ): Response {
        $payload = $request->getContent();
        $data = json_decode($payload, true);

        // Validate event type
        if (!isset($data['type']) || $data['type'] !== 'checkout.session.completed') {
            return new Response('Invalid event type', 400);
        }

        $session = $data['data']['object'];
        $email = $session['customer_email'] ?? null;
        $sessionId = $session['id'] ?? null;

        if (!$email || !$sessionId) {
            return new Response('Missing email or session ID', 400);
        }

        // Retrieve user from email
        $user = $userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            return new Response('User not found', 404);
        }

        // Get user's cart items
        $cartItems = $cartRepo->findBy(['user' => $user]);
        if (empty($cartItems)) {
            return new Response('Empty cart', 200);
        }

        // Create order entity
        $order = new Orders();
        $order->setUser($user);
        $order->setStripeSessionId($sessionId);
        $order->setStatus('paid');
        $order->setCreatedAt(new \DateTimeImmutable());

        $total = 0;

        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);

            // Handle lesson purchase
            if ($lesson = $cartItem->getLesson()) {
                $orderItem->setType('lesson');
                $orderItem->setLabel($lesson->getName());
                $orderItem->setPrice($lesson->getPrice());
                $orderItem->setLesson($lesson);

                // Grant access to lesson
                $userLesson = new UserLesson();
                $userLesson->setUser($user);
                $userLesson->setLesson($lesson);
                $em->persist($userLesson);

                $total += $lesson->getPrice();
            }

            // Handle cursus purchase
            elseif ($cursus = $cartItem->getCursus()) {
                $orderItem->setType('cursus');
                $orderItem->setLabel($cursus->getName());
                $orderItem->setPrice($cursus->getPrice());
                $orderItem->setCursus($cursus);

                // Grant access to all lessons in cursus
                foreach ($cursus->getLessons() as $lesson) {
                    $userLesson = new UserLesson();
                    $userLesson->setUser($user);
                    $userLesson->setLesson($lesson);
                    $em->persist($userLesson);
                }

                $total += $cursus->getPrice();
            }

            // Add order item and cleanup cart
            $em->persist($orderItem);
            $order->addItem($orderItem);
            $em->remove($cartItem);
        }

        // Finalize order
        $order->setTotal($total);
        $em->persist($order);

        // Persist everything
        $em->flush();

        return new Response('Webhook processed', 200);
    }
}
