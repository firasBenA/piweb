<?php

namespace App\Form;

use App\Entity\Medecin;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;//pour valider fichier mta3 certif
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints\Regex; //pour valider numtel


class MedecinType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            //->add('motDePasse')
            ->add('motDePasse', PasswordType::class, [
                'mapped' => false, // Ne pas mapper directement à l'entité
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un mot de passe']),
                    new Length(['min' => 6, 'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères']),
                ],
            ])
            ->add('confirmMotDePasse', PasswordType::class, [
                'mapped' => false, // Ne pas mapper directement à l'entité
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez confirmer votre mot de passe']),
                ],
            ])


            ->add('specialite')
            #->add('certificat')
            ->add('certificat', FileType::class, [ // Champ pour télécharger un fichier
                'label' => 'Certificat (image)',
                'mapped' => false, // Ne pas mapper directement à l'entité
                'required' => false, // Facultatif
                'constraints' => [
                    new File([
                        'maxSize' => '1024k', // Limite de taille (1 Mo)
                        'mimeTypes' => [ // Types MIME autorisés
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                    ]),
                ],
            ])
            ->add('adresse')
            ->add('age')
            ->add('sexe', ChoiceType::class, [ // Modifiez ce champ
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'placeholder' => 'Sélectionnez votre sexe', // Optionnel : texte par défaut
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner votre sexe.']),
                ],
                ])

                ->add('telephone', TelType::class, [
                    'label' => 'Numéro de Téléphone',
                    'constraints' => [
                        new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone.']),
                        new Regex([
                            'pattern' => '/^\d{8}$/',
                            'message' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
                        ]),
                    ],
                ])

            ->add('imageDeProfil', null,[
                'required' => false, 
                'attr' => [
                'placeholder' => 'URL de l\'image de profil (facultatif)',
    ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Medecin::class,
        ]);
    }
}
