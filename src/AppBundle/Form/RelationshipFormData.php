<?php

namespace AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;


class RelationshipFormData
{
    /**
     * @var string
     */
    public $uuid = '';

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $sourceNodeUuid = '';

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $targetNodeUuid = '';

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $type = '';

    /** @var PropertyFormData[] */
    public $properties = [];
}
