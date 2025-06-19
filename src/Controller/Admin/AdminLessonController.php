<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Entity\Cursus;
use App\Entity\Lesson;
use App\Form\AdminLessonType;
use App\Form\Model\LessonCreationData;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ThemeRepository;
use App\Repository\CursusRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Controller for managing lessons in the admin interface.
 *
 * Handles listing, creation, editing, and deletion of lessons,
 * with the ability to associate or create related Theme and Cursus entities.
 */
class AdminLessonController extends AbstractController
{
    /**
     * Displays a list of all lessons with their related cursus and theme.
     *
     * @param LessonRepository $lessonRepository The lesson repository to retrieve data
     * @return Response The rendered lesson list view
     */
    #[Route('/admin/formation', name: 'admin_lesson_list')]
    public function list(LessonRepository $lessonRepository): Response
    {
        $lessons = $lessonRepository->findAll();

        return $this->render('admin/adminLessonsList.html.twig', [
            'lessons' => $lessons,
        ]);
    }

    /**
     * Displays and handles the form to create a new lesson.
     *
     * Allows selecting an existing Theme or Cursus, or creating new ones,
     * and associates them to the newly created Lesson.
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @param ThemeRepository $themeRepo Repository for Theme entities
     * @param CursusRepository $cursusRepo Repository for Cursus entities
     * @return Response The form view or redirect upon success
     */
    #[Route('/admin/formation/creation', name: 'admin_lesson_create')]
    public function create(Request $request, EntityManagerInterface $em, ThemeRepository $themeRepo, CursusRepository $cursusRepo): Response
    {
        $data = new LessonCreationData();
        $form = $this->createForm(AdminLessonType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Theme
            if ($data->isNewTheme && $data->newThemeName) {
                $theme = new Theme();
                $theme->setName($data->newThemeName);
                $theme->setCreatedBy($this->getUser());
                $theme->setUpdatedBy($this->getUser());
                $em->persist($theme);
            } else {
                $theme = $data->selectedThemeId;
            }

            // Handle Cursus
            if ($data->isNewCursus && $data->newCursusName && $data->newCursusPrice !== null) {
                $cursus = new Cursus();
                $cursus->setName($data->newCursusName);
                $cursus->setPrice($data->newCursusPrice);
                $cursus->setTheme($theme);
                $cursus->setCreatedBy($this->getUser());
                $cursus->setUpdatedBy($this->getUser());
                $em->persist($cursus);
            } else {
                $cursus = $data->selectedCursusId;
            }

            // Handle Lesson
            $lesson = new Lesson();
            $lesson->setName($data->lessonName);
            $lesson->setPrice($data->lessonPrice);
            $lesson->setContentText($data->contentText);
            $lesson->setContentVideoUrl($data->contentVideoUrl);
            $lesson->setIsValidated(false);
            $lesson->setCursus($cursus);
            $lesson->setCreatedBy($this->getUser());
            $lesson->setUpdatedBy($this->getUser());

            $em->persist($lesson);
            $em->flush();

            $this->addFlash('success', 'Lesson successfully created.');
            return $this->redirectToRoute('admin_lesson_list');
        }

        return $this->render('admin/adminLessonsAdd.html.twig', [
            'form' => $form->createView(),
            'lesson' => null,
        ]);
    }

    
    /**
     * Fetches cursus list by theme ID for dynamic form update.
     *
     * @param Request $request
     * @param CursusRepository $cursusRepo
     * @return JsonResponse
     */
    #[Route('/admin/formation/cursus-par-theme', name: 'admin_cursus_by_theme', methods: ['GET'])]
    public function getCursusByTheme(Request $request, CursusRepository $cursusRepo): JsonResponse
    {
        $themeId = $request->query->get('themeId');

        if (!$themeId) {
            return new JsonResponse(['error' => 'Theme ID missing'], 400);
        }

        $cursusList = $cursusRepo->findBy(['theme' => $themeId]);

        $result = [];
        foreach ($cursusList as $cursus) {
            $result[] = [
                'id' => $cursus->getId(),
                'name' => $cursus->getName(),
            ];
        }

        return new JsonResponse($result);
    }

    /**
     * Displays and handles the form to edit an existing lesson.
     *
     * Allows changing the associated Theme and Cursus or creating new ones.
     *
     * @param Lesson $lesson The lesson to edit
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @param ThemeRepository $themeRepo Repository for Theme entities
     * @param CursusRepository $cursusRepo Repository for Cursus entities
     * @return Response The form view or redirect upon success
     */
    #[Route('/admin/formation/{id}', name: 'admin_lesson_edit')]
    public function edit(
        Lesson $lesson,
        Request $request,
        EntityManagerInterface $em,
        ThemeRepository $themeRepo,
        CursusRepository $cursusRepo
    ): Response {
        // Hydrate DTO from the existing lesson
        $data = new LessonCreationData();
        $data->lessonName = $lesson->getName();
        $data->lessonPrice = $lesson->getPrice();
        $data->contentText = $lesson->getContentText();
        $data->contentVideoUrl = $lesson->getContentVideoUrl();
        $data->selectedThemeId = $lesson->getCursus()?->getTheme();
        $data->selectedCursusId = $lesson->getCursus();

        $form = $this->createForm(AdminLessonType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Theme
            if ($data->isNewTheme && $data->newThemeName) {
                $theme = new Theme();
                $theme->setName($data->newThemeName);
                $theme->setCreatedBy($this->getUser());
                $theme->setUpdatedBy($this->getUser());
                $em->persist($theme);
            } else {
                $theme = $data->selectedThemeId;
            }

            // Handle Cursus
            if ($data->isNewCursus && $data->newCursusName && $data->newCursusPrice !== null) {
                $cursus = new Cursus();
                $cursus->setName($data->newCursusName);
                $cursus->setPrice($data->newCursusPrice);
                $cursus->setTheme($theme);
                $cursus->setCreatedBy($this->getUser());
                $cursus->setUpdatedBy($this->getUser());
                $em->persist($cursus);
            } else {
                $cursus = $data->selectedCursusId;
            }

            // Update lesson
            $lesson->setName($data->lessonName);
            $lesson->setPrice($data->lessonPrice);
            $lesson->setContentText($data->contentText);
            $lesson->setContentVideoUrl($data->contentVideoUrl);
            $lesson->setCursus($cursus);
            $lesson->setUpdatedBy($this->getUser());
            $lesson->setUpdatedAt(new \DateTimeImmutable());

            $em->flush();

            $this->addFlash('success', 'Lesson successfully updated.');
            return $this->redirectToRoute('admin_lesson_list');
        }

        return $this->render('admin/adminLessonsAdd.html.twig', [
            'form' => $form->createView(),
            'lesson' => $lesson,
        ]);
    }

    /**
     * Deletes a lesson after validating the CSRF token.
     *
     * @param Request $request The current HTTP request
     * @param Lesson $lesson The lesson to delete
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @return Response Redirect to the list after deletion
     */
    #[Route('/admin/formation/{id}/delete', name: 'admin_lesson_delete', methods: ['POST'])]
    public function delete(Request $request, Lesson $lesson, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_lesson_' . $lesson->getId(), $request->request->get('_token'))) {
            $em->remove($lesson);
            $em->flush();
            $this->addFlash('success', 'Lesson successfully deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Deletion failed.');
        }

        return $this->redirectToRoute('admin_lesson_list');
    }

}
