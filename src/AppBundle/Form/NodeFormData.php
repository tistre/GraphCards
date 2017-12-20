<?php

namespace AppBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;


class NodeFormData
{
    /** @var string */
    public $uuid = '';

    /** @var string[] */
    public $labels = [];

    /** @var PropertyFormData[] */
    public $properties = [];
}
