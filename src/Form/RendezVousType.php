<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\Medecin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
    ->add('date', DateType::class, [
        'widget' => 'single_text',
        'label' => 'Date du rendez-vous',
        'required' => false,
        'by_reference' => true,
        'constraints' => [new NotBlank(['message'=>'Pick a date!']),
                    new NotNull(['message'=>'Pick a date!'])],
        'attr' => [
            'class' => 'form-control',
            'placeholder' => 'Sélectionnez une date',
        ],
    ])

            ->add('type_rdv', ChoiceType::class, [
                'choices' => [
                    'Consultation' => 'consultation',
                    'Suivi' => 'suivi',
                    'Urgence' => 'urgence',
                ],
                'label' => 'Type de rendez-vous',
                'required' => true, // Requis pour validation
                'attr' => [
                    'class' => 'form-control',
                ],
                'placeholder' => 'Sélectionnez un type de rendez-vous',
            ])
            ->add('cause', TextType::class, [
                'label' => 'Cause du rendez-vous',
                'required' => true, // Requis pour validation
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez la raison du rendez-vous',
                    
                ],
                
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => 'nom',
                'label' => 'Médecin',
                'required' => true, // Requis pour validation
                'attr' => [
                    'class' => 'form-control',
                ],
                'placeholder' => 'Sélectionnez un médecin',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
