<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'label' => 'Nom',
            
        ])
        ->add('prenom', TextType::class, [
            'label' => 'Prénom',
            
        ])
        ->add('sexe', ChoiceType::class, [
            'label' => 'Sexe',
            'choices' => [
                'Homme' => 'homme',
                'Femme' => 'femme',
                'Autre' => 'autre',
            ],
            'placeholder' => 'Sélectionnez votre sexe',
            'required' => false,
        ])
        ->add('adresse', TextType::class, [
            'label' => 'Adresse',
            'required' => false,
        ])
    
        ->add('age', NumberType::class, [
            'label' => 'Âge',
            
        ])
        ->add('telephone', TextType::class, [
            'label' => 'Numéro de Téléphone',
            
        ])
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Patient' => 'patient',
                    'Médecin' => 'medecin',
                ],
                'placeholder' => 'Sélectionnez votre rôle',
                'mapped' => false,
            ])
            ->add('specialite', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
            ])
            ->add('certificat', FileType::class, [
                'label' => 'Certificat',
                'required' => false,
            ])
            ->add('imageProfil', FileType::class, [
                'label' => 'Image de Profil',
                'required' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
