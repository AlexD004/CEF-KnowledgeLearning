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
use Symfony\Component\Form\FormInterface;


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
     * and associates them to the newly created Lesson. Also supports image
     * upload for new Theme and Cursus entities.
     *
     * @param Request $request The current HTTP request
     * @param EntityManagerInterface $em The Doctrine entity manager
     * @param ThemeRepository $themeRepo Repository for Theme entities
     * @param CursusRepository $cursusRepo Repository for Cursus entities
     * @return Response The form view or redirect upon success
     */
    #[Route('/admin/formation/creation', name: 'admin_lesson_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ThemeRepository $themeRepo,
        CursusRepository $cursusRepo
    ): Response {
        $data = new LessonCreationData();

        $form = $this->createForm(AdminLessonType::class, $data, [
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                /** @var LessonCreationData $data */
                $data = $form->getData();

                if ($data->isNewTheme) {
                    $groups[] = 'new_theme';
                }

                if ($data->isNewCursus) {
                    $groups[] = 'new_cursus';
                }

                return $groups;
            },
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Theme
            if ($data->isNewTheme && $data->newThemeName) {
                $theme = new Theme();
                $theme->setName($data->newThemeName);
                $theme->setCreatedBy($this->getUser());
                $theme->setUpdatedBy($this->getUser());

                if ($data->newThemeImage) {
                    $filename = uniqid('theme_') . '.' . $data->newThemeImage->guessExtension();
                    $data->newThemeImage->move($this->getParameter('images_directory'), $filename);
                    $theme->setImage($filename);
                }

                $em->persist($theme);
            } else {
                $theme = $data->selectedThemeId;
            }

            // Cursus
            if ($data->isNewCursus && $data->newCursusName && $data->newCursusPrice !== null) {
                $cursus = new Cursus();
                $cursus->setName($data->newCursusName);
                $cursus->setPrice($data->newCursusPrice);
                $cursus->setTheme($theme);
                $cursus->setCreatedBy($this->getUser());
                $cursus->setUpdatedBy($this->getUser());

                if ($data->newCursusImage) {
                    $filename = uniqid('cursus_') . '.' . $data->newCursusImage->guessExtension();
                    $data->newCursusImage->move($this->getParameter('images_directory'), $filename);
                    $cursus->setImage($filename);
                }

                $em->persist($cursus);
            } else {
                $cursus = $data->selectedCursusId;
            }

            // Lesson
            $lesson = new Lesson();
            $lesson->setName($data->lessonName);
            $lesson->setPrice($data->lessonPrice);
            $lesson->setContentText($data->contentText);
            $lesson->setContentVideoUrl($data->contentVideoUrl);
            $lesson->setDescription($data->description);
            $lesson->setIsValidated(false);
            $lesson->setCursus($cursus);
            $lesson->setCreatedBy($this->getUser());
            $lesson->setUpdatedBy($this->getUser());

            if ($data->image) {
                $filename = uniqid('lesson_') . '.' . $data->image->guessExtension();
                $data->image->move($this->getParameter('images_directory'), $filename);
                $lesson->setImage($filename);
            }

            $em->persist($lesson);
            $em->flush();

            $this->addFlash('success', 'Formation créée avec succès.');
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
     * Fetches the Theme associated with a given Cursus ID.
     *
     * This endpoint is used in the admin interface to automatically
     * select the correct Theme when a Cursus is chosen in the lesson form.
     * It returns a JSON response with the Theme's ID and name.
     *
     * Example request: /admin/formation/theme-par-cursus?cursusId=5
     *
     * @param Request $request The current HTTP request containing the cursusId as query parameter
     * @param CursusRepository $cursusRepo The repository used to retrieve the Cursus and its Theme
     *
     * @return JsonResponse A JSON response containing the Theme ID and name,
     *                      or an error message with appropriate status code
     */
    #[Route('/admin/formation/theme-par-cursus', name: 'admin_theme_by_cursus', methods: ['GET'])]
    public function getThemeByCursus(Request $request, CursusRepository $cursusRepo): JsonResponse
    {
        $cursusId = $request->query->get('cursusId');

        if (!$cursusId) {
            return new JsonResponse(['error' => 'Cursus ID missing'], 400);
        }

        $cursus = $cursusRepo->find($cursusId);
        if (!$cursus || !$cursus->getTheme()) {
            return new JsonResponse(['error' => 'Theme not found'], 404);
        }

        $theme = $cursus->getTheme();

        return new JsonResponse([
            'id' => $theme->getId(),
            'name' => $theme->getName(),
        ]);
    }


   /**
     * Displays and handles the form to edit an existing lesson.
     *
     * Allows changing the associated Theme and Cursus or creating new ones.
     * Also handles image upload for newly created Theme and Cursus during edit.
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
        $data = new LessonCreationData();
        $data->lessonName = $lesson->getName();
        $data->lessonPrice = $lesson->getPrice();
        $data->contentText = $lesson->getContentText();
        $data->contentVideoUrl = $lesson->getContentVideoUrl();
        $data->description = $lesson->getDescription();
        $data->selectedThemeId = $lesson->getCursus()?->getTheme();
        $data->selectedCursusId = $lesson->getCursus();
        $data->image = null; // on ne remplit pas l’image existante dans un champ file

        $form = $this->createForm(AdminLessonType::class, $data, [
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                /** @var LessonCreationData $data */
                $data = $form->getData();

                if ($data->isNewTheme) {
                    $groups[] = 'new_theme';
                }

                if ($data->isNewCursus) {
                    $groups[] = 'new_cursus';
                }

                return $groups;
            },
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Theme
            if ($data->isNewTheme && $data->newThemeName) {
                $theme = new Theme();
                $theme->setName($data->newThemeName);
                $theme->setCreatedBy($this->getUser());
                $theme->setUpdatedBy($this->getUser());

                if ($data->newThemeImage) {
                    $filename = uniqid('theme_') . '.' . $data->newThemeImage->guessExtension();
                    $data->newThemeImage->move($this->getParameter('images_directory'), $filename);
                    $theme->setImage($filename);
                }

                $em->persist($theme);
            } else {
                $theme = $data->selectedThemeId;
            }

            // Cursus
            if ($data->isNewCursus && $data->newCursusName && $data->newCursusPrice !== null) {
                $cursus = new Cursus();
                $cursus->setName($data->newCursusName);
                $cursus->setPrice($data->newCursusPrice);
                $cursus->setTheme($theme);
                $cursus->setCreatedBy($this->getUser());
                $cursus->setUpdatedBy($this->getUser());

                if ($data->newCursusImage) {
                    $filename = uniqid('cursus_') . '.' . $data->newCursusImage->guessExtension();
                    $data->newCursusImage->move($this->getParameter('images_directory'), $filename);
                    $cursus->setImage($filename);
                }

                $em->persist($cursus);
            } else {
                $cursus = $data->selectedCursusId;
            }

            // Lesson
            $lesson->setName($data->lessonName);
            $lesson->setPrice($data->lessonPrice);
            $lesson->setContentText($data->contentText);
            $lesson->setContentVideoUrl($data->contentVideoUrl);
            $lesson->setDescription($data->description);
            $lesson->setCursus($cursus);
            $lesson->setUpdatedBy($this->getUser());
            $lesson->setUpdatedAt(new \DateTimeImmutable());

            if ($data->image) {
                $filename = uniqid('lesson_') . '.' . $data->image->guessExtension();
                $data->image->move($this->getParameter('images_directory'), $filename);
                $lesson->setImage($filename);
            }

            $em->flush();

            $this->addFlash('success', 'Formation mise à jour avec succès.');
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
