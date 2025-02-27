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
use Symfony\Component\Form\FormInterface;
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
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'constraints' => [
                new NotBlank([
                    'message' => 'Entrer votre mot de passe',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'ton mot de passe doit étre {{ limit }} caractére',
                    'max' => 4096,
                ]),
            ],
        ])
        ->add('roles', ChoiceType::class, [
            'label' => 'Rôle',
            'mapped' => false,
            'choices' => [
            'Patient' => 'ROLE_PATIENT',
            'Médecin' => 'ROEL_MEDECIN',
            ],
            'multiple' => false, 
            'placeholder' => 'Sélectionnez votre rôle',
            'required' => true,
            'constraints' => [
            new NotBlank([
                'message' => 'Veuillez sélectionner un rôle',
            ]),
            ],
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
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($form->get('roles')->getData() === 'MEDECIN') 
                    {
                    return ['Default', 'medecin'];
                }
                return ['Default'];
            },
        ]);
    }
}
