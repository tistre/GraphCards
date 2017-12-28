<?php

namespace AppBundle;

use AppBundle\Form\NodeFormData;
use AppBundle\Form\PropertyFormData;
use AppBundle\Form\RelationshipFormData;
use GraphCards\Db\DbAdapter;
use GraphCards\Model\Node;
use GraphCards\Model\Property;
use GraphCards\Model\Relationship;


class DataSource
{
    /** @var DbAdapter */
    protected $dbAdapter;


    /**
     * DataSource constructor.
     * @param DbAdapter $dbAdapter
     */
    public function __construct(DbAdapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
    }


    /**
     * @param NodeFormData $formData
     * @return Node
     */
    public function createNodeFromFormData(NodeFormData $formData): Node
    {
        $node = new Node();

        $node->setLabels($formData->labels);

        if (strlen($formData->uuid) > 0) {
            $property = (new Property())
                ->setName('uuid')
                ->setValue($formData->uuid);

            $node->setProperty($property);
        }

        foreach ($formData->properties as $propertyFormData) {
            $property = (new Property())
                ->setName($propertyFormData->name)
                ->setType($propertyFormData->type)
                ->setValue($propertyFormData->value);

            $node->setProperty($property);
        }

        return $this->dbAdapter->createNode($node);
    }


    /**
     * @param NodeFormData $formData
     * @return Node
     */
    public function updateNodeFromFormData(NodeFormData $formData): Node
    {
        $node = $this->dbAdapter->loadNode($formData->uuid);

        $node->setLabels($formData->labels);

        foreach ($formData->properties as $propertyFormData) {
            if ($propertyFormData->name === 'uuid') {
                continue;
            }

            if (strlen(trim($propertyFormData->name)) === 0) {
                continue;
            }

            $property = (new Property())
                ->setName($propertyFormData->name)
                ->setType($propertyFormData->type)
                ->setValue($propertyFormData->value);

            $node->setProperty($property);
        }

        return $this->dbAdapter->updateNode($node);
    }


    /**
     * @return NodeFormData
     */
    public function getAddNodeFormData(): NodeFormData
    {
        $formData = new NodeFormData();

        $formData->labels = [''];
        $formData->properties = [];
        $formData->properties[] = new PropertyFormData();

        return $formData;
    }


    /**
     * @param $nodeUuid
     * @return NodeFormData
     */
    public function getEditNodeFormData($nodeUuid): NodeFormData
    {
        $node = $this->dbAdapter->loadNode($nodeUuid);

        $formData = new NodeFormData();

        $formData->uuid = $node->getUuid();
        $formData->labels = $node->getLabels();

        $formData->properties = [];

        foreach ($node->getProperties() as $nodeProperty) {
            if ($nodeProperty->getName() === 'uuid') {
                continue;
            }

            $propertyFormData = new PropertyFormData();
            $propertyFormData->name = $nodeProperty->getName();
            $propertyFormData->type = $nodeProperty->getType();
            $propertyFormData->value = $nodeProperty->getValue();

            $formData->properties[] = $propertyFormData;
        }

        // Allow adding data
        $formData->labels[] = '';
        $formData->properties[] = new PropertyFormData();

        return $formData;
    }


    /**
     * @return RelationshipFormData
     */
    public function getAddRelationshipFormData(): RelationshipFormData
    {
        $formData = new RelationshipFormData();

        $formData->sourceNodeUuid = '';
        $formData->targetNodeUuid = '';
        $formData->type = '';
        $formData->properties = [];
        $formData->properties[] = new PropertyFormData();

        return $formData;
    }


    /**
     * @param $relationshipUuid
     * @return RelationshipFormData
     */
    public function getEditRelationshipFormData($relationshipUuid): RelationshipFormData
    {
        $relationship = $this->dbAdapter->loadRelationship($relationshipUuid);

        $formData = new RelationshipFormData();

        $formData->uuid = $relationship->getUuid();
        $formData->sourceNodeUuid = $relationship->getSourceNode()->getUuid();
        $formData->targetNodeUuid = $relationship->getTargetNode()->getUuid();
        $formData->type = $relationship->getType();

        $formData->properties = [];

        foreach ($relationship->getProperties() as $relationshipProperty) {
            if ($relationshipProperty->getName() === 'uuid') {
                continue;
            }

            $propertyFormData = new PropertyFormData();
            $propertyFormData->name = $relationshipProperty->getName();
            $propertyFormData->type = $relationshipProperty->getType();
            $propertyFormData->value = $relationshipProperty->getValue();

            $formData->properties[] = $propertyFormData;
        }

        // Allow adding data
        $formData->properties[] = new PropertyFormData();

        return $formData;
    }


    /**
     * @param RelationshipFormData $formData
     * @return Relationship
     */
    public function createRelationshipFromFormData(RelationshipFormData $formData): Relationship
    {
        $relationship = new Relationship();

        $relationship->setType($formData->type);
        $relationship->setSourceNode((new Node())->setUuid($formData->sourceNodeUuid));
        $relationship->setTargetNode((new Node())->setUuid($formData->targetNodeUuid));

        if (strlen($formData->uuid) > 0) {
            $property = (new Property())
                ->setName('uuid')
                ->setValue($formData->uuid);

            $relationship->setProperty($property);
        }

        foreach ($formData->properties as $propertyFormData) {
            $property = (new Property())
                ->setName($propertyFormData->name)
                ->setType($propertyFormData->type)
                ->setValue($propertyFormData->value);

            $relationship->setProperty($property);
        }

        return $this->dbAdapter->createRelationship($relationship);
    }


    /**
     * @param RelationshipFormData $formData
     * @return Relationship
     */
    public function updateRelationshipFromFormData(RelationshipFormData $formData): Relationship
    {
        $relationship = $this->dbAdapter->loadRelationship($formData->uuid);

        $relationship->setType($formData->type);
        $relationship->setSourceNode((new Node())->setUuid($formData->sourceNodeUuid));
        $relationship->setTargetNode((new Node())->setUuid($formData->targetNodeUuid));

        foreach ($formData->properties as $propertyFormData) {
            if ($propertyFormData->name === 'uuid') {
                continue;
            }

            if (strlen(trim($propertyFormData->name)) === 0) {
                continue;
            }

            $property = (new Property())
                ->setName($propertyFormData->name)
                ->setType($propertyFormData->type)
                ->setValue($propertyFormData->value);

            $relationship->setProperty($property);
        }

        return $this->dbAdapter->updateRelationship($relationship);
    }
}