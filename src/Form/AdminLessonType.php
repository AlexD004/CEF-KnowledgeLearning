<?php

namespace App\Form;

use App\Entity\Cursus;
use App\Entity\Theme;
use App\Form\Model\LessonCreationData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{
    CheckboxType, MoneyType, TextType
};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Service\Admin\LessonFormSubscriber;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


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
                'label' => 'Cochez pour créer un nouveau thème',
                'required' => false,
                'label_attr' => ['class' => 'check-label'],
                'row_attr' => ['class' => 'check-wrap'],
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
            ->add('newThemeImage', FileType::class, [
            'label' => 'Image du nouveau thème',
            'required' => false,
            'mapped' => true,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                    'mimeTypesMessage' => 'Format accepté : JPG, PNG, WebP',
                ])
            ],
        ])

            // Cursus section
            ->add('isNewCursus', CheckboxType::class, [
                'label' => 'Cochez pour créer un nouveau cursus',
                'required' => false,
                'label_attr' => ['class' => 'check-label'],
                'row_attr' => ['class' => 'check-wrap'],
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
            ->add('newCursusImage', FileType::class, [
                'label' => 'Image du nouveau cursus',
                'required' => false,
                'mapped' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format accepté : JPG, PNG, WebP',
                    ])
                ],
            ])
            ->add('newCursusPrice', MoneyType::class, [
                'label' => 'Prix du nouveau cursus',
                'currency' => false,
                'attr' => ['placeholder' => '0,00 €'],
                'html5' => true,
                'attr' => [
                    'class' => 'money-input',
                    'inputmode' => 'decimal',
                    'step' => '0.01',
                    'min' => '0'
                ],
                'row_attr' => ['class' => 'money-wrap'],
                'required' => false,
            ])

            // Lesson details
            ->add('lessonName', TextType::class, [
                'label' => 'Nom de la formation',
            ])
            ->add('lessonPrice', MoneyType::class, [
                'label' => 'Prix de la formation',
                'currency' => false,
                'attr' => ['placeholder' => '0,00 €'],
                'html5' => true,
                'attr' => [
                    'class' => 'money-input',
                    'inputmode' => 'decimal',
                    'step' => '0.01', 
                    'min' => '0'
                ],
                'row_attr' => ['class' => 'money-wrap']
            ])
            ->add('contentText', CKEditorType::class, [
                'label' => 'Contenu texte de la formation',
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo de la formation',
                'required' => false,
                'mapped' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => ['video/mp4', 'video/webm'],
                        'mimeTypesMessage' => 'Formats acceptés : MP4, WebM',
                    ]),
                ],
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Description de la formation',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de la formation',
                'required' => false,
                'mapped' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Format accepté : JPG, PNG, WebP',
                    ])
                ],
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
