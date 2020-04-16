<?php

namespace CaT\Plugins\CopySettings;

trait Helper
{
    /**
     * Translate var with plugin lang
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }
}
