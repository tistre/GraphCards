<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PropertyFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => PropertyFormData::class
            ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add
            (
                'name',
                TextType::class,
                [
                    'required' => false
                ]
            )
            ->add
            (
                'value',
                TextType::class,
                [
                    'required' => false
                ]
            );
    }
}