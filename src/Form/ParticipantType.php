<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, ['label' => 'Pseudo :'])
            ->add('prenom', TextType::class, ['label' => 'Prénom :'])
            ->add('nom', TextType::class, ['label' => 'Nom :'])
            ->add('telephone', TelType::class, ['label' => 'Téléphone'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('champ', FileType::class,
                [ 'mapped' => false, // désactive le mappage avec le champ dans l'entité (qui attend une chaîne de caractère)
                ]
            )

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => 'Mot de passe :',
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez le mot de passe'],
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern'=> '/^(?=.*\d)(?=.*[A-Z])(?=.*[@#$%])(?!.*(.)\1{2}).*[a-z]/',
                        'message'=> 'Votre mot de passe doit comporter au moins six caractères, dont des lettres majuscules et minuscules, un chiffre et un symbole ( @ # $ % ).'
                    ])
                ],
            ])
            ->add('Campus',EntityType::class,['class' => Campus::class,
                'choice_label' => 'nom'
            ])
           ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
