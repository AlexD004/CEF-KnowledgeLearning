<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\Cursus;
use App\Entity\Theme;
use App\Entity\CartItem;
use App\Entity\Orders;
use App\Entity\OrderItem;
use App\Entity\UserLesson;
use App\Service\PurchaseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Integration test for the PurchaseService.
 *
 * This test uses the actual test database to simulate the real purchase flow,
 * ensuring that orders, order items, and user lessons are properly created.
 */
class PurchaseServiceTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private PurchaseService $purchaseService;

    /**
     * Boot the Symfony kernel and prepare services.
     */
    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->purchaseService = static::getContainer()->get(PurchaseService::class);

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');

        $tables = ['order_item', 'orders', 'user_lesson', 'lesson', 'cart_item', 'user'];
        foreach ($tables as $table) {
            $connection->executeStatement($platform->getTruncateTableSQL($table, true));
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Test that PurchaseService correctly creates an order and links a lesson
     * to the user when purchasing a single lesson.
     */
    public function testProcessPurchaseCreatesOrderAndGrantsLessonAccess(): void
    {
        // Create a test user
        $user = new User();
        $user->setEmail('buyer@test.com');
        $user->setFirstName('Buyer');
        $user->setLastName('Test');
        $user->setPassword('hashedPassword');
        $user->setRoles(['ROLE_CLIENT']);
        $this->em->persist($user);

        $theme = new Theme();
        $theme->setName('Thème de test');
        $this->em->persist($theme);

        $cursus = new Cursus();
        $cursus->setName('Cursus test');
        $cursus->setPrice(200);
        $cursus->setTheme($theme);
        $this->em->persist($cursus);

        $lesson = new Lesson();
        $lesson->setName('Leçon test');
        $lesson->setPrice(100);
        $lesson->setContentText('Contenu pédagogique.');
        $lesson->setCursus($cursus);
        $this->em->persist($lesson);

        $this->em->flush();


        // Create a cart item with the lesson
        $cartItem = new CartItem();
        $cartItem->setUser($user);
        $cartItem->setLesson($lesson);
        $this->em->persist($cartItem);
        $this->em->flush();

        // Simulate Stripe session payload
        $session = [
            'id' => 'test_session_123',
            'customer_email' => 'buyer@test.com',
        ];

        // Execute purchase
        $this->purchaseService->processSuccessfulPayment($session);

        // Assert that an order was created
        $orders = $this->em->getRepository(Orders::class)->findBy(['user' => $user]);
        $this->assertCount(1, $orders);
        $order = $orders[0];
        $this->assertSame('paid', $order->getStatus());
        $this->assertEquals(100, $order->getTotal());
        $this->assertEquals('test_session_123', $order->getStripeSessionId());

        // Assert order contains correct item
        $orderItems = $order->getItems();
        $this->assertCount(1, $orderItems);
        $item = $orderItems[0];
        $this->assertSame('lesson', $item->getType());
        $this->assertSame('Leçon test', $item->getLabel());
        $this->assertEquals(100, $item->getPrice());

        // Assert user gained access to the lesson
        $userLessons = $this->em->getRepository(UserLesson::class)->findBy(['user' => $user]);
        $this->assertCount(1, $userLessons);
        $this->assertSame($lesson->getId(), $userLessons[0]->getLesson()->getId());

        // Assert cart is now empty
        $remainingCart = $this->em->getRepository(CartItem::class)->findBy(['user' => $user]);
        $this->assertCount(0, $remainingCart);
    }
}
