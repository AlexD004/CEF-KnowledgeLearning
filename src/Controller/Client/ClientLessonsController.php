<?php

namespace App\Controller\Client;

use App\Entity\Lesson;
use App\Entity\UserLesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserLessonRepository;
use App\Service\User\UserCertificationService;

/**
 * Controller responsible for displaying purchased lessons to logged-in users.
 */
#[IsGranted('ROLE_CLIENT')]
class ClientLessonsController extends AbstractController
{
    /**
     * Displays the list of purchased lessons, grouped by cursus.
     *
     * This view is only available for authenticated users with ROLE_USER.
     * Each cursus displays all its lessons. Purchased lessons are shown normally,
     * while others are visually locked.
     *
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/apprenant/formations', name: 'client_formations')]
    public function showPurchasedLessons(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Retrieve all lessons purchased by the current user
        $userLessonEntries = $em->getRepository(UserLesson::class)->findBy(['user' => $user]);

        $purchasedLessonIds = [];
        $validatedLessonIds = [];

        foreach ($userLessonEntries as $userLesson) {
            $lesson = $userLesson->getLesson();
            $lessonId = $lesson->getId();

            $purchasedLessonIds[] = $lessonId;

            if ($userLesson->isValidated()) {
                $validatedLessonIds[] = $lessonId;
            }

            $cursusId = $lesson->getCursus()->getId();

            if (!isset($lessonsGroupedByCursus[$cursusId])) {
                $lessonsGroupedByCursus[$cursusId] = [
                    'cursus' => $lesson->getCursus(),
                    'purchasedLessons' => [],
                ];
            }

            $lessonsGroupedByCursus[$cursusId]['purchasedLessons'][] = $lesson;
        }

        // Group lessons by cursus
        $lessonsGroupedByCursus = [];

        foreach ($userLessonEntries as $userLesson) {
            $lesson = $userLesson->getLesson();
            $cursus = $lesson->getCursus();
            $cursusId = $cursus->getId();

            if (!isset($lessonsGroupedByCursus[$cursusId])) {
                $lessonsGroupedByCursus[$cursusId] = [
                    'cursus' => $cursus,
                    'purchasedLessons' => [],
                ];
            }

            $lessonsGroupedByCursus[$cursusId]['purchasedLessons'][] = $lesson;
        }

        return $this->render('client/userFormations.html.twig', [
            'lessonsGroupedByCursus' => $lessonsGroupedByCursus,
            'purchasedLessonIds' => $purchasedLessonIds,
            'validatedLessonIds' => $validatedLessonIds,
        ]);
    }

    /**
     * Displays the lesson content for a purchased lesson.
     *
     * Ensures that only lessons purchased by the current user are accessible.
     *
     * @param int $id
     * @param EntityManagerInterface $em
     * @return Response
     */
    #[Route('/apprenant/formation/{id}', name: 'client_lesson_show', requirements: ['id' => '\d+'])]
    public function showLesson(int $id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Check if the lesson exists
        $lesson = $em->getRepository(Lesson::class)->find($id);
        if (!$lesson) {
            throw $this->createNotFoundException('Formation introuvable.');
        }

        // Ensure the current user has access to the lesson
        $userLesson = $em->getRepository(UserLesson::class)->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
        ]);

        if (!$userLesson) {
            throw $this->createAccessDeniedException("Vous n'avez pas accès à cette formation.");
        }

        return $this->render('client/lesson_show.html.twig', [
            'lesson' => $lesson,
            'userLesson' => $userLesson,
        ]);
    }


    #[Route('/apprenant/formation/{id}/valider', name: 'client_lesson_validate', methods: ['POST'])]
    public function validateLesson(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserLessonRepository $userLessonRepository,
        Security $security,
        UserCertificationService $certificationService
    ): Response {
        $user = $security->getUser();
        $lesson = $em->getRepository(Lesson::class)->find($id);

        if (!$lesson) {
            throw $this->createNotFoundException('Formation introuvable.');
        }

        $userLesson = $userLessonRepository->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
        ]);

        if (!$userLesson) {
            $this->addFlash('error', "Vous n'avez pas accès à cette formation.");
            return $this->redirectToRoute('client_formations');
        }

        // Protection CSRF
        if (!$this->isCsrfTokenValid('validate_lesson_'.$lesson->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Échec de sécurité. Veuillez réessayer.');
            return $this->redirectToRoute('client_lesson_show', ['id' => $lesson->getId()]);
        }

        if ($userLesson->isValidated()) {
            $this->addFlash('info', 'Cette leçon est déjà validée.');
        } else {
            $userLesson->setIsValidated(true);
            $em->flush();
            $certificationService->checkAndCreateCertification($user, $lesson);
            $this->addFlash('success', 'Leçon validée avec succès !');
        }

        return $this->redirectToRoute('client_formations');
    }
}
