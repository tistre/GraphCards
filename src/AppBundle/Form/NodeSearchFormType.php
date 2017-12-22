<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NodeSearchFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => NodeSearchFormData::class,
                'csrf_protection' => false
            ]);
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add
            (
                'label',
                SearchType::class,
                [
                    'label' => 'Label',
                    'required' => false
                ]
            )
            ->add
            (
                'x',
                SubmitType::class,
                [
                    'label' => 'Search'
                ]
            );
    }
}