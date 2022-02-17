<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieFiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'label' => 'Campus'
            ])
            ->add('nom', TextType::class,[
                'label' => 'Le nom de la sortie contient: ',
                'required' => false,
                'empty_data' => ''
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5'=> true,
                'widget'=> 'single_text',
                'label' => 'Entre ',
                'required' => false,
                'mapped' => false
            ])
            ->add('dateHeureFin', DateTimeType::class, [
                     'html5'=> true,
                     'widget'=> 'single_text',
                     'label' => ' et ',
                     'required' => false,
                'mapped' => false
                 ])
                 ->add('organisateur', CheckboxType::class, [
                     'label' => 'Sorties dont je suis l\'organisateur/trice',
                     'required' => false,
                     'mapped' => false
                     ])
                 ->add('inscrit', CheckboxType::class, [
                     'label' => 'Sorties auxquelles je suis inscrit/e',
                     'required' => false,
                     'mapped' => false

                 ])
                 ->add('non_inscrit', CheckboxType::class, [
                     'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                     'required' => false,
                     'mapped' => false

                 ])
                 ->add('passees', CheckboxType::class, [
                     'label' => 'Sorties passées',
                     'required' => false,
                     'mapped' => false
                 ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
