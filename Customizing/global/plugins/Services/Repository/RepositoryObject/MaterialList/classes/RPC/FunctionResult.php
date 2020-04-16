<?php

namespace CaT\Plugins\MaterialList\RPC;

class FunctionResult
{
    public function __construct($title, $value)
    {
        assert('is_string($title)');

        $this->title = $title;
        $this->value = $value;
    }

    /**
     * Get the title for xlsx export
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get the value for xlsx export
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
