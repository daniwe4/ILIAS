<?php

namespace CaT\Plugins\MaterialList\RPC;

use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class FolderReadTest extends TestCase
{
    public function setUp() : void
    {
        $txt = function ($code) {
            return $code;
        };
        $this->procedure_loader = new FolderReader($txt);
    }

    public function test_customFunctions()
    {
        $files = $this->procedure_loader->getCustomFunctionOptions(__DIR__ . "/../files/Custom");
        $this->assertEquals(2, count($files));

        $keys = array_keys($files);
        $this->assertEquals($keys, array("fncOne", "fncTwo"));
    }

    public function test_courseFunctions()
    {
        $files = $this->procedure_loader->getCourseFunctionOptions(__DIR__ . "/../files/Course");
        $this->assertEquals(2, count($files));

        $keys = array_keys($files);
        $this->assertEquals(array("fncFour", "fncThree"), $keys);
    }
}
