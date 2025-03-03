<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'required' => true,
                'attr' => [
                    'maxlength' => 255,
                    'placeholder' => 'Ex: Doliprane, Ibuprofène...'
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'maxlength' => 1000,
                    'placeholder' => 'Ex: Médicament pour...'
                ],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'required' => true,
                'currency' => 'DTN',       // Or another currency
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,         // For decimal precision
                ],
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock disponible',
                'required' => true,
                'attr' => [
                    'min' => 0,            // No negative stock
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPEG ou PNG)',
                'required' => false,
                'mapped' => false,     // So it doesn't directly set Produit::image
                'data_class' => null,  // No associated class, so we get an UploadedFile
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}