<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{TextType, EmailType, PasswordType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines the user profile edit form for clients.
 * 
 * Includes the following fields:
 * - First name
 * - Last name
 * - Email
 * - Plain password (optional, not mapped directly)
 */
class UserType extends AbstractType
{
    /**
     * Builds the user profile form.
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Votre mot de passe doit contenir au moins une lettre majuscule.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/\d/',
                        'message' => 'Votre mot de passe doit contenir au moins un chiffre.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/[\W]/',
                        'message' => 'Votre mot de passe doit contenir au moins un caractère spécial (ex: !@#$%^&*).',
                    ]),
                ],
            ]);
    }

    /**
     * Configures options for this form type.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
