<?php

namespace CaT\Plugins\MaterialList\RPC;

/**
 * Baseclass for each procedure implementation
 */
abstract class ilFunctionBase
{
    /**
     * @var \ilObjCourse
     */
    protected $crs;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(\ilObjCourse $crs, \Closure $txt)
    {
        $this->crs = $crs;
        $this->txt = $txt;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }

    /**
     * Run method to execute the function
     *
     * @return mixed
     */
    abstract public function run();
}
