<?php

namespace App\Controller\Public;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\User;
use App\Entity\Theme;
use App\Form\RegistrationTypeForm;
use App\Service\User\UserRegistrationService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ThemeRepository;

final class PublicController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(ThemeRepository $themeRepo): Response
    {
        $themes = $themeRepo->findAll();

        return $this->render('public/index.html.twig', [
            'themes' => $themes,
        ]);
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserRegistrationService $registrationService): Response
    {
        $user = new User();

        $form = $this->createForm(RegistrationTypeForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $registrationService->register($user, $plainPassword);

            $this->addFlash('success', 'Your account has been created. You can now log in.');

            return $this->redirectToRoute('login');
        }

        return $this->render('public/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/theme/{id}', name: 'theme_show', requirements: ['id' => '\d+'])]
    public function showTheme(Theme $theme): Response
    {
        return $this->render('public/theme_show.html.twig', [
            'theme' => $theme,
        ]);
    }
}
