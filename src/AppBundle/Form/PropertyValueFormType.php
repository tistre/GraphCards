<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PropertyValueFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => PropertyValueFormData::class
            ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add
            (
                'type',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => [
                        'String' => 'string',
                        'Integer' => 'integer',
                        'Float' => 'float',
                        'Boolean' => 'boolean'
                    ]
                ]
            )
            ->add
            (
                'value',
                TextareaType::class,
                [
                    'required' => false
                ]
            );
    }
}