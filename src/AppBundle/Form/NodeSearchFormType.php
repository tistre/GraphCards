<?php

namespace AppBundle\Form;

use AppBundle\Service\DbAdapterService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class NodeSearchFormType extends AbstractType
{
    /** @var DbAdapterService */
    protected $dbAdapterService;


    public function __construct(DbAdapterService $dbAdapterService)
    {
        $this->dbAdapterService = $dbAdapterService;
    }


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
            ->add(
                'q',
                TextType::class,
                [
                    'label' => 'Query',
                    'required' => false
                ]
            )
            ->add(
                'label',
                ChoiceType::class,
                [
                    'label' => 'Label',
                    'required' => false,
                    'choices' => $this->getNodeLabelChoices()
                ]
            )
            ->add(
                's',
                SubmitType::class,
                [
                    'label' => 'Search'
                ]
            );
    }


    /**
     * @return string[]
     */
    protected function getNodeLabelChoices(): array
    {
        $result = [];

        foreach ($this->dbAdapterService->getDbAdapter()->listNodeLabels() as $nodeLabel) {
            $result[$nodeLabel] = $nodeLabel;
        }

        return $result;
    }
}