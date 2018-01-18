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
     * @return string[]
     */
    public function getValues(): array
    {
        $result = [];

        foreach ($this->property->getValues() as $propertyValue) {
            $result[] = (string)$propertyValue->getValue();
        }

        return $result;
    }


    /**
     * @return string
     */
    public function getFirstValue(): string
    {
        return (string)$this->property->getFirstValue()->getValue();
    }
}