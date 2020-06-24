<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Types;

/**
 */
class StringType extends UnstructuredType
{
    /**
     * @inheritdocs
     */
    public function repr()
    {
        return "string";
    }

    /**
     * @inheritdocs
     */
    public function contains($value)
    {
        return
            is_string($value)
            || is_integer($value)
            || $value === false
            ;
    }

    /**
     * @inheritdocs
     */
    public function unflatten(array &$value)
    {
        $name = $this->repr();
        if (count($value) == 0) {
            throw new \InvalidArgumentException("Expected $name, found nothing.");
        }

        $val = array_shift($value);
        if (!$this->contains($val)) {
            throw new \InvalidArgumentException("Expected $name, found '$val'");
        }

        if ($val === false) {
            $val = '';
        }

        return $val;
    }
}
