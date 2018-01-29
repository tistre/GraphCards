<?php

namespace AppBundle\ViewModel;

use GraphCards\Model\Property;
use GraphCards\Model\Relationship;


class RelationshipViewModel
{
    /** @var Relationship */
    protected $relationship;

    /** @var NodeViewModel */
    protected $sourceNode;

    /** @var NodeViewModel */
    protected $targetNode;

    /** @var \Twig_Environment */
    protected $twig;

    /** @var array */
    protected $displayTemplates = [];

    /**
     * RelationshipViewModel constructor.
     * @param Relationship $relationship
     */
    public function __construct(Relationship $relationship, \Twig_Environment $twig, $displayTemplates)
    {
        $this->relationship = $relationship;
        $this->twig = $twig;

        if (is_array($displayTemplates)) {
            $this->displayTemplates = $displayTemplates;
        }
    }


    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->relationship->getType();
    }


    /**
     * @return NodeViewModel
     */
    public function getSourceNode(): NodeViewModel
    {
        // TODO Load node so templates can get the name
        if (!$this->sourceNode) {
            $node = $this->relationship->getSourceNode();

            $this->sourceNode = new NodeViewModel(
                $node,
                $this->twig,
                $this->displayTemplates
            );
        }

        return $this->sourceNode;
    }


    /**
     * @return NodeViewModel
     */
    public function getTargetNode(): NodeViewModel
    {
        return new NodeViewModel(
            $this->relationship->getTargetNode(),
            $this->twig,
            $this->displayTemplates
        );
    }


    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty(string $name): bool
    {
        return $this->relationship->hasProperty($name);
    }


    /**
     * @param string $name
     * @return PropertyViewModel
     */
    public function getProperty(string $name): PropertyViewModel
    {
        if (!$this->relationship->hasProperty($name)) {
            $property = (new Property())->setName($name);
        } else {
            $property = $this->relationship->getProperty($name);
        }

        return new PropertyViewModel($property);
    }


    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->relationship->getUuid();
    }
}