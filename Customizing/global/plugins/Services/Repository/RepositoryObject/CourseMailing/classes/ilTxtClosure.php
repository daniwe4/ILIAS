<?php
namespace CaT\Plugins\CourseMailing;

trait ilTxtClosure
{

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}
