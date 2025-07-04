<?php

namespace App\Service;

use App\Entity\Orders;
use App\Entity\OrderItem;
use App\Entity\UserLesson;
use App\Repository\UserRepository;
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class PurchaseService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private CartItemRepository $cartRepo
    ) {}

    /**
     * Handles the post-payment logic for a Stripe session.
     *
     * @param array $session Stripe session array from the webhook
     * @return void
     */
    public function processSuccessfulPayment(array $session): void
    {
        $email = $session['customer_email'] ?? $session['customer_details']['email'] ?? null;
        $sessionId = $session['id'] ?? null;

        if (!$email || !$sessionId) {
            return; // Log ou throw dans une vraie version
        }

        $user = $this->userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            return;
        }

        $cartItems = $this->cartRepo->findBy(['user' => $user]);
        if (empty($cartItems)) {
            return;
        }

        $order = new Orders();
        $order->setUser($user);
        $order->setStripeSessionId($sessionId);
        $order->setStatus('paid');
        $order->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')));

        $total = 0;

        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrder($order);

            if ($lesson = $cartItem->getLesson()) {
                $orderItem->setType('lesson');
                $orderItem->setLabel($lesson->getName());
                $orderItem->setPrice($lesson->getPrice());
                $orderItem->setLesson($lesson);

                $userLesson = new UserLesson();
                $userLesson->setUser($user);
                $userLesson->setLesson($lesson);
                $this->em->persist($userLesson);

                $total += $lesson->getPrice();
            } elseif ($cursus = $cartItem->getCursus()) {
                $orderItem->setType('cursus');
                $orderItem->setLabel($cursus->getName());
                $orderItem->setPrice($cursus->getPrice());
                $orderItem->setCursus($cursus);

                foreach ($cursus->getLessons() as $lesson) {
                    $userLesson = new UserLesson();
                    $userLesson->setUser($user);
                    $userLesson->setLesson($lesson);
                    $this->em->persist($userLesson);
                }

                $total += $cursus->getPrice();
            }

            $this->em->persist($orderItem);
            $order->addItem($orderItem);
            $this->em->remove($cartItem);
        }

        $order->setTotal($total);
        $this->em->persist($order);
        $this->em->flush();
    }
}
