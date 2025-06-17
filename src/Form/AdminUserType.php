<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{TextType, EmailType, PasswordType, ChoiceType};
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

/**
 * Form used by administrators to create or edit users.
 * 
 * Includes personal information and role management.
 * Handles optional password updates.
 * 
 * Fields:
 * - First name
 * - Last name
 * - Email
 * - Roles (multi-select)
 * - Plain password (optional, not mapped directly)
 */
class AdminUserType extends AbstractType
{
    /**
     * Builds the admin user form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array $options Additional options for building the form
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Client' => 'ROLE_CLIENT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => false,
                'multiple' => false,
                'mapped' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false,
                'required' => false,
            ]);
    }

    /**
     * Configures the default options for this form type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
