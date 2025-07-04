<?php

namespace App\Service\User;

use App\Entity\User;
use App\Entity\Lesson;
use App\Entity\UserCertification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserCertificationRepository;

class UserCertificationService
{
    private EntityManagerInterface $em;
    private UserCertificationRepository $userCertificationRepository;

    public function __construct(EntityManagerInterface $em, UserCertificationRepository $userCertificationRepository)
    {
        $this->em = $em;
        $this->userCertificationRepository = $userCertificationRepository;
    }

    /**
     * Checks if the user has validated all lessons of the cursus.
     * If yes, creates a certification entry.
     */
    public function checkAndCreateCertification(User $user, Lesson $validatedLesson): void
    {
        $cursus = $validatedLesson->getCursus();
        $allLessons = $cursus->getLessons();

        foreach ($allLessons as $lesson) {
            $userLesson = $this->em->getRepository(\App\Entity\UserLesson::class)->findOneBy([
                'user' => $user,
                'lesson' => $lesson,
            ]);

            if (!$userLesson || !$userLesson->isValidated()) {
                return;
            }
        }

        $existing = $this->userCertificationRepository->findOneBy([
            'user' => $user,
            'cursus' => $cursus,
        ]);

        if (!$existing) {
            $certification = new UserCertification();
            $certification->setUser($user);
            $certification->setCursus($cursus);

            $this->em->persist($certification);
            $this->em->flush();
        }
    }
}
