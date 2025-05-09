<?php
namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\User;
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
        // You can get the list of medecins (users with ROLE_MEDECIN) from the controller
        $medecins = $options['medecins']; 

        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
                'required' => false,
                'by_reference' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Pick a date!']),
                    new NotNull(['message' => 'Pick a date!'])
                ],
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
                'class' => User::class, // Use User class instead of Medecin
                'choices' => $medecins, // Only show users with ROLE_MEDECIN
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
            'medecins' => [], // Default to an empty array if no medecins are passed
        ]);
    }
}