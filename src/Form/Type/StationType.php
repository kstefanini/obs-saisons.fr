<?php

namespace App\Form\Type;

use App\Entity\Station;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, ['required' => true])
            ->add('description', TextareaType::class, ['required' => false])
            ->add('latitude', TextType::class, ['required' => true])
            ->add('longitude', TextType::class, ['required' => true])
            ->add('locality', TextType::class, ['required' => true])
            ->add('insee_code', TextType::class, ['required' => true])
            ->add('altitude', HiddenType::class)
            ->add('habitat', ChoiceType::class, [
                'choices' => [
                    'Ville' => 'Ville',
                    'Campagne' => 'Campagne',
                ],
                'required' => true,
            ])
            ->add('is_private', CheckboxType::class, ['required' => false])
            ->add('header_image', FileType::class, [
                'required' => false,
                'label' => 'Image de la station',
                'attr' => [
                    'class' => 'upload-input',
                    'accept' => 'image/png, image/jpeg',
                ],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Station::class,
        ]);
    }
}