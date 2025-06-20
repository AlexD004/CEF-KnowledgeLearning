<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Theme;
use App\Form\Model\LessonCreationData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType, MoneyType, TextareaType, TextType, UrlType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Service\Admin\LessonFormSubscriber;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


/**
 * Symfony Form type used to create or update a Lesson.
 *
 * This form supports selecting or creating a Theme and/or Cursus dynamically.
 * It binds to a LessonCreationData DTO to keep concerns separated from Doctrine entities.
 */
class AdminLessonType extends AbstractType
{

    private LessonFormSubscriber $subscriber;

    public function __construct(LessonFormSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Builds the form fields for lesson creation.
     *
     * Includes:
     * - Theme selection or creation
     * - Cursus selection or creation
     * - Lesson-specific fields
     *
     * @param FormBuilderInterface $builder The form builder interface
     * @param array $options Options passed to the form (none expected here)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Theme section
            ->add('isNewTheme', CheckboxType::class, [
                'label' => 'Souhaitez-vous créer un nouveau thème ?',
                'required' => false,
            ])
            ->add('selectedThemeId', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'label' => 'Thème existant',
                'required' => false,
                'placeholder' => 'Choisir un thème',
            ])
            ->add('newThemeName', TextType::class, [
                'label' => 'Nom du nouveau thème',
                'required' => false,
            ])

            // Cursus section
            ->add('isNewCursus', CheckboxType::class, [
                'label' => 'Souhaitez-vous créer un nouveau cursus ?',
                'required' => false,
            ])
            ->add('selectedCursusId', EntityType::class, [
                'class' => Cursus::class,
                'choice_label' => 'name',
                'label' => 'Cursus existant',
                'required' => false,
                'placeholder' => 'Choisir un cursus',
            ])
            ->add('newCursusName', TextType::class, [
                'label' => 'Nom du nouveau cursus',
                'required' => false,
            ])
            ->add('newCursusPrice', MoneyType::class, [
                'label' => 'Prix du nouveau cursus',
                'required' => false,
                'currency' => 'EUR',
            ])

            // Lesson details
            ->add('lessonName', TextType::class, [
                'label' => 'Nom de la formation',
            ])
            ->add('lessonPrice', MoneyType::class, [
                'label' => 'Prix de la formation',
                'currency' => 'EUR',
            ])
            ->add('contentText', CKEditorType::class, [
                'label' => 'Contenu texte de la formation',
            ])
            ->add('contentVideoUrl', UrlType::class, [
                'label' => 'Lien de la vidéo de formation',
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Description de la formation',
                'required' => false,
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (URL ou chemin)',
                'required' => false,
            ]);
            
        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * Configures the form to use the LessonCreationData class as data holder.
     *
     * @param OptionsResolver $resolver The resolver to configure form options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LessonCreationData::class,
        ]);
    }
}
