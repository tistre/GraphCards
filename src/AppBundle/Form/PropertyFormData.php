<?php

namespace AppBundle\Form;

use GraphCards\Model\Property;


class PropertyFormData
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $type = Property::TYPE_STRING;

    /** @var string */
    public $value = '';
}
