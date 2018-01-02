<?php

namespace AppBundle\ViewModel;

use GraphCards\Model\Property;


class PropertyViewModel
{
    /** @var Property */
    protected $property;


    /**
     * PropertyViewModel constructor.
     * @param Property $property
     */
    public function __construct(Property $property)
    {
        $this->property = $property;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        // TODO: Add getLabel() method fetching name from a node with this property name as URI?
        return $this->property->getName();
    }


    /**
     * @return string
     */
    public function getValue(): string
    {
        return (string)$this->property->getValue();
    }
}