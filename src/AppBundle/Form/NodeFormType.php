<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NodeFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => NodeFormData::class
            ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add
            (
                'labels',
                CollectionType::class,
                [
                    'entry_type' => TextType::class,
                    'required' => false
                ]
            )
            ->add
            (
                'properties',
                CollectionType::class,
                [
                    'entry_type' => PropertyFormType::class,
                    'required' => false
                ]
            )
            ->add
            (
                'save',
                SubmitType::class,
                [
                    'label' => 'Save'
                ]
            );
    }
}