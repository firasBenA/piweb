<?php

// src/Form/DiagnostiqueType.php

namespace App\Form;

use App\Entity\Diagnostique;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DiagnostiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $symptomsDict = $options['symptoms_dict']; // Access the symptoms_dict from the options

        $builder
            ->add('selectedSymptoms', TextareaType::class, [
                'mapped' => true,
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true,
                    'style' => 'width: 100%; padding: 10px;',
                    'placeholder' => 'Entrer vos symptomes'
                ],
                'required' => true,
            ])
            ->add('zoneCorps', TextType::class, [
                'label' => 'Zone du Corps',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter la zone du corps'
                ],
                'required' => true,  // Ensure the field is required
            ])
            ->add('dateSymptomes', DateType::class, [
                'label' => 'Date of Symptoms',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Select the date when symptoms began'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save it!',
                'attr' => ['class' => 'btn btn-primary', 'id' => 'submitSymptoms', 'style' => 'width: 100%; margin-bottom:8px;'],

            ]);
    }



    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Diagnostique::class,
            'symptoms_dict' => [],
        ]);
    }
}
