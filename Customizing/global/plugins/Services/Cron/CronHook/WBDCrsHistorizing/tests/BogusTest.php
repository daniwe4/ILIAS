<?php

namespace CaT\Plugins\WBDCrsHistorizing;

use PHPUnit\Framework\TestCase;

/**
 * Sample for PHP Unit tests
 */
class BogusTest extends TestCase
{
    public function test_successfull()
    {
        $test_var = "Peter";

        $this->assertEquals("Peter", $test_var);
    }

    public function test_failed()
    {
        try {
            $this->checkValue("Bernd");
            $this->assertFalse("Should have raised.");
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    protected function checkValue($value)
    {
        if ($value != "Peter") {
            throw new \Exception("Value is wrong");
        }
    }
}
