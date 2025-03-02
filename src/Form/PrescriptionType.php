<?php

// src/Form/PrescriptionType.php
namespace App\Form;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Prescription;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
            ])
            ->add('contenue', TextType::class, [
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
            ])
            ->add('datePrescription', DateType::class)
            ->add('diagnostique', EntityType::class, [
                'class' => Diagnostique::class,
                'choice_label' => 'nom',
                'disabled' => true // Use the custom option

            ])
            
            ->add('save', SubmitType::class, ['label' => 'Create Prescription']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
            'disabled_diagnostique' => false, // Default is false, but controller sets it to true

        ]);
    }
}
