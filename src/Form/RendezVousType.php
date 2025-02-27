<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
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
            // Champ date, obligatoire
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date du rendez-vous',
                'required' => true, // La date est obligatoire
                'by_reference' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une date.']),
                    new NotNull(['message' => '']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Sélectionnez une date',
                ],
            ])
            // Type de rendez-vous
            ->add('type_rdv', ChoiceType::class, [
                'choices' => [
                    'Consultation' => 'consultation',
                    'Suivi' => 'suivi',
                    'Urgence' => 'urgence',
                ],
                'label' => 'Type de rendez-vous',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'placeholder' => 'Sélectionnez un type de rendez-vous',
            ])
            // Cause du rendez-vous
            ->add('cause', TextType::class, [
                'label' => 'Cause du rendez-vous',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Décrivez la raison du rendez-vous',
                ],
            ])
            // Médecin
            ->add('medecin', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er) {
                    // Utilisation d'une requête plus précise pour vérifier les rôles des médecins
                    return $er->createQueryBuilder('u')
    ->where("u.roles LIKE :role")
    ->setParameter('role', '%"MEDECIN"%');

    
                },
                'choice_label' => function (User $user) {
                    // Affichage du nom et prénom du médecin
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'label' => 'Médecin',
                'placeholder' => 'Sélectionnez un médecin',
                'required' => true, // Assurez-vous que la sélection est obligatoire
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}
