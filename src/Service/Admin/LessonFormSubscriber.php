<?php

namespace App\Service\Admin;

use App\Entity\Cursus;
use App\Entity\Theme;
use App\Repository\CursusRepository;
use App\Repository\ThemeRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;

/**
 * Subscriber to dynamically filter the cursus list
 * based on the selected theme in the lesson creation form.
 */
class LessonFormSubscriber implements EventSubscriberInterface
{
    private CursusRepository $cursusRepo;
    private ThemeRepository $themeRepo;

    public function __construct(CursusRepository $cursusRepo, ThemeRepository $themeRepo)
    {
        $this->cursusRepo = $cursusRepo;
        $this->themeRepo = $themeRepo;
    }

    /**
     * Returns the form events this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }

    /**
     * Updates the cursus field with a list of cursus belonging to the given theme.
     *
     * @param FormInterface $form
     * @param Theme|null $theme
     */
    private function updateCursusField(FormInterface $form, ?Theme $theme): void
    {
        $cursusList = $theme ? $this->cursusRepo->findBy(['theme' => $theme]) : [];

        $form->add('selectedCursusId', EntityType::class, [
            'class' => Cursus::class,
            'choices' => $cursusList,
            'choice_label' => 'name',
            'placeholder' => 'Choisir un cursus',
            'label' => 'Cursus existant',
            'required' => false,
        ]);
    }

    /**
     * Initializes the cursus choices when the form is first built.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        $this->updateCursusField($form, $data->selectedThemeId ?? null);
    }

    /**
     * Updates the cursus field based on submitted theme ID.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (!empty($data['selectedThemeId'])) {
            $theme = $this->themeRepo->find($data['selectedThemeId']);
            $this->updateCursusField($form, $theme);
        }
    }
}
