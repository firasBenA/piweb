<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\Medecin;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
            ])
            ->add('type_rdv', ChoiceType::class, [
                'choices' => [
                    'Consultation' => 'consultation',
                    'Suivi' => 'suivi',
                    'Urgence' => 'urgence',
                ],
                'label' => 'Type de rendez-vous',
            ])
            ->add('cause', TextType::class)
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => 'nom',
                'label' => 'Médecin',
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'Réserver',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
