<?php

namespace App\Form;

use App\Entity\RendezVous;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ModifConType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateType::class, [
            'widget' => 'single_text',
            'label' => 'Date du rendez-vous',
            'required' => true,
            'by_reference' => true,
            'constraints' => [
                new NotBlank(['message' => 'Veuillez choisir une date.']),
                new NotNull(['message' => 'La date ne peut pas être vide.']),
                new GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'La date ne peut pas être antérieure à aujourd\'hui.'
                ])
            ],
            'attr' => [
                'class' => 'form-control',
                'min' => (new \DateTime())->format('Y-m-d'), // Empêche la sélection d'une date passée
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