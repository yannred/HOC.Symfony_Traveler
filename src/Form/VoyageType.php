<?php

namespace App\Form;

use App\Entity\Destination;
use App\Entity\Voyage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoyageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Nom du voyage'
            ])
            ->add('destination', EntityType::class, [
                'class' => Destination::class,
                'choice_label' => 'ville',
                'label' => 'Destination'
            ])
            ->add('description')
            ->add('note')
            ->add('dateDepart', DateTimeType::class, [
                'label' => 'Date de départ',
                'html5' => false
            ])
            ->add('photos', FileType::class, [
                'label' => 'Photos du voyage',
                'mapped' => false,
                'multiple' => true,
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Voyage::class,
        ]);
    }
}
