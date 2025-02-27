<?php
namespace App\Form;
 
use App\Entity\ConsultationHis;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultationHisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            
           
            ->add('notesMed', TextType::class, [
                'label' => 'Notes Médicales',
            ])
            ->add('traitement', TextType::class, [
                'label' => 'Traitement',
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée (en minutes)',
            ]);
            
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConsultationHis::class,
        ]);
    }
}
