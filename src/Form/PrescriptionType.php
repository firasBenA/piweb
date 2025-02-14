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
            ->add('titre', TextType::class)
            ->add('contenue', TextType::class)
            ->add('datePrescription', DateType::class)
            ->add('diagnostique', EntityType::class, [
                'class' => Diagnostique::class,  // This specifies that the field refers to the DossierMedical entity
                'choice_label' => 'nom',            // You can customize this to show any field from DossierMedical, like 'name'
            ])
            ->add('save', SubmitType::class, ['label' => 'Create Prescription']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
