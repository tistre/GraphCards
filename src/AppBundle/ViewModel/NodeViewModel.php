<?php

namespace AppBundle\ViewModel;

use GraphCards\Model\Node;
use GraphCards\Model\Property;


class NodeViewModel
{
    /** @var Node */
    protected $node;


    /**
     * NodeViewModel constructor.
     * @param Node $node
     */
    public function __construct(Node $node)
    {
        $this->node = $node;
    }


    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->node->getLabels();
    }


    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return $this->node->hasProperty($name);
    }


    /**
     * @param string $name
     * @return PropertyViewModel
     */
    public function getProperty(string $name): PropertyViewModel
    {
        if (!$this->node->hasProperty($name)) {
            $property = (new Property())->setName($name);
        } else {
            $property = $this->node->getProperty($name);
        }

        return new PropertyViewModel($property);
    }


    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->node->getUuid();
    }
}