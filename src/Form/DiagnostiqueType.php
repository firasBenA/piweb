<?php

// src/Form/DiagnostiqueType.php

namespace App\Form;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Symptomes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class DiagnostiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Retrieve symptoms from the database
        $symptoms = $options['symptoms']; // List of Symptomes

        $builder
            ->add('dossierMedical', EntityType::class, [
                'class' => DossierMedical::class,
                'choice_label' => 'id', // Adapter selon le besoin
                'label' => 'Dossier Medical ID',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Dossier Medical ID cannot be empty.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Diagnose'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Diagnostique::class,
            'symptoms' => [],  // List of symptoms to pass to the form
            'symptoms_dict' => [], // ✅ Add this to resolve the error
            'dossierMedicalId' => null,  // DossierMedical ID for the form
        ]);
    }
}
