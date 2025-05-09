<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('oldPassword', PasswordType::class, [
            'label' => 'Ancien mot de passe',
            'mapped' => false, // This field is not mapped to the entity
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez entrer votre ancien mot de passe.',
                ]),
            ],
        ])
        ->add('newPassword', PasswordType::class, [
            'label' => 'Nouveau mot de passe',
            'mapped' => false, // This field is not mapped to the entity
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez entrer un nouveau mot de passe.',
                ]),
                new Assert\Length([
                    'min' => 6,
                    'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('confirmPassword', PasswordType::class, [
            'label' => 'Confirmer le nouveau mot de passe',
            'mapped' => false, // This field is not mapped to the entity
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez confirmer votre nouveau mot de passe.',
                ]),
            ],
        ]);
}
}
