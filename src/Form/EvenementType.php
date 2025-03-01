<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Article;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
            ])
            ->add('contenue', TextType::class, [
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
                
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Conférence' => 'conference',
                    'Séminaire' => 'seminaire',
                    'Workshop' => 'workshop',
                    'Formation' => 'formation'
                ],
                'required' => true,
                'empty_data' => '',
                'attr' => ['class' => 'form-control'],
            ])
            
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Planifié' => 'planifie',
                    'En cours' => 'en_cours',
                    'Terminé' => 'termine',
                ],
                  'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
                
            ])
            ->add('lieux_event', TextType::class, [
                'attr' => ['maxlength' => 255],
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
            ])
            ->add('date_event', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'empty_data' => '', // Forces an empty string if the field is left empty
                'attr' => ['class' => 'form-control'],
                
            ])
              
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'titre',
                'multiple' => true,
                'expanded' => true, // Display as checkboxes
            ]);
            
        ;   
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
