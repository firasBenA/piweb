<?php

namespace App\Form;

use App\Entity\ArticleBoutique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArticleBoutiqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est obligatoire.']),
                ],
            ])
            ->add('prix', NumberType::class, [
                'required' => true,
                'attr' => ['step' => '0.01'], // Allows decimal values
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prix est obligatoire.']),
                    new Assert\Positive(['message' => 'Le prix doit être un nombre positif.']),
                ],
                'empty_data' => '0.00', // Prevents empty data errors
            ])
            ->add('creeLe', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner une date.']),
                    new Assert\Date(['message' => 'Format de date invalide.']),
                ],
            ])
            ->add('stock', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le stock est obligatoire.']),
                    new Assert\PositiveOrZero(['message' => 'Le stock ne peut pas être négatif.']),
                ],
                'attr' => ['class' => 'stock-input'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, PNG, GIF)',
                'mapped' => false,  // Prevents automatic database saving for the file
                'required' => false, // Make the field optional
                'attr' => ['class' => 'form-control-file'], // Add class for styling
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '8M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, GIF).',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleBoutique::class,
        ]);
    }
}
