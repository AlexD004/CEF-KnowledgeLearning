<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = $this->client->getContainer();

        $entityManager = $container->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
    }

    public function testLoginWithValidCredentials(): void
    {
        $container = $this->client->getContainer();

        $entityManager = $container->get('doctrine.orm.entity_manager');

        $user = new \App\Entity\User();
        $user->setEmail('login@test.com');
        $user->setFirstName('Login');
        $user->setLastName('Test');
        $user->setRoles(['ROLE_CLIENT']);
        $user->setIsVerified(true);

        $passwordHasher = $container->get('security.password_hasher');
        $hashedPassword = $passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        // Essai de login
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'login@test.com',
            'password' => 'password123',
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/');
    }
}
