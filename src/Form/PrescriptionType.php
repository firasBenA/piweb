<?php

namespace App\Form;

use App\Entity\Prescription;
use App\Entity\DossierMedical;
use App\Entity\Diagnostique;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenue', TextareaType::class, [
                'label' => 'Contenu de la Prescription',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('datePrscription', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de Prescription',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dossierMedical', EntityType::class, [
                'class' => DossierMedical::class,
                'choice_label' => 'id', // Change this if you have a better label
                'label' => 'Dossier Médical',
                'placeholder' => 'Sélectionnez un dossier médical',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('diagnostique', EntityType::class, [
                'class' => Diagnostique::class,
                'choice_label' => 'nom', // Assuming 'nom' is the diagnosis name
                'label' => 'Diagnostique',
                'placeholder' => 'Sélectionnez un diagnostic',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer la Prescription',
                'attr' => ['class' => 'btn btn-primary mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prescription::class,
        ]);
    }
}
