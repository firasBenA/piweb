<?php

// src/Form/DossierMedicalType.php

namespace App\Form;

use App\Entity\DossierMedical;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DossierMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datePrescription', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Prescription Date'
            ])
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => 'nom', 
                'label' => 'Patient',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Dossier'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DossierMedical::class,
        ]);
    }
}
