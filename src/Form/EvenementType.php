<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Medecin;
use App\Entity\Article;
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
                'attr' => ['maxlength' => 255]
            ])
            ->add('contenue', TextType::class, [
                'attr' => ['maxlength' => 255]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Conférence' => 'conference',
                    'Séminaire' => 'seminaire',
                    'Workshop' => 'workshop'
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Planifié' => 'planifie',
                    'En cours' => 'en_cours',
                    'Terminé' => 'termine'
                ]
            ])
            ->add('lieux_event', TextType::class, [
                'attr' => ['maxlength' => 255]
            ])
            ->add('date_event', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => 'id',
            ])
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'choice_label' => 'titre',
                'multiple' => true,
                'expanded' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
