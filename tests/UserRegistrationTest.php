<?php

namespace App\Tests;

use App\Entity\User;
use App\Service\User\UserRegistrationService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRegistrationTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
    }

    public function testUserRegistration(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $userService = $container->get(UserRegistrationService::class);

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('Test');
        $user->setLastName('User');

        $userService->register($user, 'password123');

        // Reload user from DB to assert
        $userFromDb = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'test@example.com']);

        $this->assertNotNull($userFromDb);
        $this->assertNotEmpty($userFromDb->getPassword());
        $this->assertContains('ROLE_CLIENT', $userFromDb->getRoles());
    }
}
