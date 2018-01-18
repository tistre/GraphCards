<?php

namespace AppBundle\Form;

use GraphCards\Model\PropertyValue;


class PropertyValueFormData
{
    /** @var string */
    public $type = PropertyValue::TYPE_STRING;

    /** @var string */
    public $value = '';
}
