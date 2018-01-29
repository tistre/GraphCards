<?php

namespace AppBundle\ViewModel;

use GraphCards\Model\Node;
use GraphCards\Model\Property;


class NodeViewModel
{
    /** @var Node */
    protected $node;

    /** @var array */
    protected $displayTemplates = [];

    /** @var \Twig_Environment */
    protected $twig;

    /**
     * NodeViewModel constructor.
     * @param Node $node
     * @param \Twig_Environment $twig
     * @param mixed $displayTemplates
     */
    public function __construct(Node $node, \Twig_Environment $twig, $displayTemplates)
    {
        $this->node = $node;
        $this->twig = $twig;

        if (is_array($displayTemplates)) {
            $this->displayTemplates = $displayTemplates;
        }
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        if (isset($this->displayTemplates['node_name_by_label']) && is_array($this->displayTemplates['node_name_by_label'])) {
            foreach ($this->getLabels() as $label) {
                if (isset($this->displayTemplates['node_name_by_label'][$label])) {
                    try {
                        $template = $this->twig->createTemplate($this->displayTemplates['node_name_by_label'][$label]);
                        return $template->render(['node' => $this]);
                    } catch (\Throwable $e) {
                        return '';
                    }
                }
            }
        }

        if (!empty($this->displayTemplates['node_name_default'])) {
            try {
                $template = $this->twig->createTemplate($this->displayTemplates['node_name_default']);
                return $template->render(['node' => $this]);
            } catch (\Throwable $e) {
                return '';
            }
        }

        return '';
    }


    /**
     * @return string[]
     */
    public function getLabels(): array
    {
        return $this->node->getLabels();
    }


    /**
     * @return PropertyViewModel[]
     */
    public function getProperties(): array
    {
        $result = [];

        foreach ($this->node->getProperties() as $property) {
            $result[] = new PropertyViewModel($property);
        }

        return $result;
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