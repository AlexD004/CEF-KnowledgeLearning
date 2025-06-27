<?php

namespace App\Controller\Client;

use App\Entity\Lesson;
use App\Entity\UserLesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller to securely stream training videos to authenticated and authorized clients.
 */
#[IsGranted('ROLE_CLIENT')]
class VideoController extends AbstractController
{
    /**
     * Streams a video file for a given lesson, only if:
     * - the user is authenticated as a client
     * - the lesson exists
     * - the lesson is associated with the current user
     * - the request comes from a valid referer (page inside the app)
     *
     * @param int $id The lesson ID
     * @param EntityManagerInterface $em Doctrine entity manager
     * @param Request $request The current HTTP request
     * @return Response The streamed video response or a rendered error page
     */
    #[Route('/video/lesson/{id}', name: 'secure_video_lesson')]
    public function streamLessonVideo(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();

        // Check if lesson exists
        $lesson = $em->getRepository(Lesson::class)->find($id);
        if (!$lesson) {
            throw new NotFoundHttpException('Lesson not found.');
        }

        // Check if user owns the lesson
        $userLesson = $em->getRepository(UserLesson::class)->findOneBy([
            'user' => $user,
            'lesson' => $lesson,
        ]);
        if (!$userLesson) {
            return $this->render('security/access_denied.html.twig');
        }

        // Check if the referer is from a valid internal page
        $referer = $request->headers->get('referer');
        $expectedPrefix = $request->getSchemeAndHttpHost() . '/apprenant/formation';
        if (!$referer || !str_starts_with($referer, $expectedPrefix)) {
            return $this->render('security/access_denied.html.twig');
        }

        // Get video path
        $videoPath = $this->getParameter('kernel.project_dir') . '/var/videos/' . $lesson->getVideoFilename();
        if (!file_exists($videoPath)) {
            throw new NotFoundHttpException('Vidéo inexistante.');
        }

        // Stream video inline (in-browser)
        $response = new BinaryFileResponse($videoPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($videoPath));

        return $response;
    }
}
