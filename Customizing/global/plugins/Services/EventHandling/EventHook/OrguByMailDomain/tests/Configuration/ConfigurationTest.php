<?php

use PHPUnit\Framework\TestCase;
use CaT\Plugins\OrguByMailDomain\Configuration\Configuration as C;

class ConfigurationTest extends TestCase
{
    public function test_init()
    {
        $c = new C(10, 'title', [1,2,3], 4, 'descr');
        $this->assertEquals($c->getTitle(), 'title');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getId(), 10);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'descr');
        return $c;
    }

    /**
     * @depends test_init
     */
    public function test_with_title($c)
    {
        $c = $c->withTitle('title1');
        $this->assertEquals($c->getTitle(), 'title1');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getId(), 10);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'descr');
    }

    /**
     * @depends test_init
     */
    public function test_with_orgu_ids($c)
    {
        $c = $c->withOrguIds([6,7]);
        $this->assertEquals($c->getTitle(), 'title');
        $this->assertEquals($c->getOrguIds(), [6,7]);
        $this->assertEquals($c->getId(), 10);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'descr');
    }

    /**
     * @depends test_init
     */
    public function test_with_position($c)
    {
        $c = $c->withPosition(8);
        $this->assertEquals($c->getTitle(), 'title');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getId(), 10);
        $this->assertEquals($c->getPosition(), 8);
        $this->assertEquals($c->getDescription(), 'descr');
    }


    /**
     * @depends test_init
     */
    public function test_with_description($c)
    {
        $c = $c->withDescription('descr1');
        $this->assertEquals($c->getTitle(), 'title');
        $this->assertEquals($c->getOrguIds(), [1,2,3]);
        $this->assertEquals($c->getId(), 10);
        $this->assertEquals($c->getPosition(), 4);
        $this->assertEquals($c->getDescription(), 'descr1');
    }
}
