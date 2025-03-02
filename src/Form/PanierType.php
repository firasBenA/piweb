<?php

namespace App\Form;

use App\Entity\ArticleBoutique;
use App\Entity\Panier;
use App\Entity\Patient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Patient selection dropdown
            ->add('user', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => function (Patient $patient) {
                    return $patient->getId() . ' - ' . $patient->getNom();
                },
                'label' => 'Patient',
                'placeholder' => 'Select a patient',
                'required' => true,
            ])
            
            // Articles selection with checkboxes and images
            ->add('articles', EntityType::class, [
                'class' => ArticleBoutique::class,
                'choice_label' => function (ArticleBoutique $article) {
                    return $article->getNom(); // This will be used for the checkbox label
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Select Articles',
                'by_reference' => false, // Ensures collection is properly managed
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Panier::class,
        ]);
    }
}
