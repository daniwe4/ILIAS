<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation\Component as IC;
use ILIAS\UI\Implementation\Component\SignalGenerator;

/**
 * Test on icon implementation.
 */
class QuickfilterTest extends ILIAS_UI_TestBase
{
    protected $options = array(
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    );

    public function getUIFactory()
    {
        $sg = new SignalGenerator();
        return new \ILIAS\UI\Implementation\Factory(
            $this->createMock(C\Counter\Factory::class),
            new IC\Glyph\Factory($sg),
            new IC\Button\Factory($sg),
            $this->createMock(C\Listing\Factory::class),
            $this->createMock(C\Image\Factory::class),
            $this->createMock(C\Panel\Factory::class),
            $this->createMock(C\Modal\Factory::class),
            $this->createMock(C\Dropzone\Factory::class),
            $this->createMock(C\Popover\Factory::class),
            $this->createMock(C\Divider\Factory::class),
            $this->createMock(C\Link\Factory::class),
            new IC\Dropdown\Factory(),
            $this->createMock(C\Item\Factory::class),
            $this->createMock(C\Icon\Factory::class),
            $this->createMock(C\ViewControl\Factory::class),
            $this->createMock(C\Chart\Factory::class),
            $this->createMock(C\Input\Factory::class),
            $this->createMock(C\Table\Factory::class),
            $this->createMock(C\MessageBox\Factory::class),
            $this->createMock(C\Card\Factory::class)
        );
    }

    private function getFactory()
    {
        $sg = new SignalGenerator();
        return new \ILIAS\UI\Implementation\Component\ViewControl\Factory($sg);
    }

    public function testConstruction()
    {
        $f = $this->getFactory();
        $quickfilter = $f->quickfilter($this->options);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\ViewControl\\Quickfilter",
            $quickfilter
        );
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Signal",
            $quickfilter->getSelectSignal()
        );
    }

    public function testAttributes()
    {
        $f = $this->getFactory();
        $quickfilter = $f->quickfilter($this->options);

        $this->assertEquals($this->options, $quickfilter->getOptions());
        $this->assertEquals('label', $quickfilter->withLabel('label')->getLabel());

        $quickfilter = $quickfilter->withTargetURL('#', 'param');
        $this->assertEquals('#', $quickfilter->getTargetURL());
        $this->assertEquals('param', $quickfilter->getParameterName());

        $this->assertEquals(array(), $quickfilter->getTriggeredSignals());
        $generator = new SignalGenerator();
        $signal = $generator->create();
        $this->assertEquals(
            $signal,
            $quickfilter->withOnSort($signal)->getTriggeredSignals()[0]->getSignal()
        );

        $quickfilter = $quickfilter->withDefaultValue('default');
        $this->assertEquals('default', $quickfilter->getDefaultValue());
    }

    public function testRendering()
    {
        $f = $this->getFactory();
        $r = $this->getDefaultRenderer();
        $quickfilter = $f->quickfilter($this->options);

        $html = $this->normalizeHTML($r->render($quickfilter));
        $this->assertEquals(
            $this->getQuickfilterExpectedHTML(),
            $html
        );
    }

    protected function getQuickfilterExpectedHTML()
    {
        $expected = <<<EOT
<div class="il-viewcontrol-quickfilter" id=""><div class="dropdown"><button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false" > <span class="caret"></span></button><ul class="dropdown-menu">
	<li><button class="btn btn-link" data-action="?quickfilter=internal_rating" id="id_1"  >Best</button></li>
	<li><button class="btn btn-link" data-action="?quickfilter=date_desc" id="id_2"  >Most Recent</button></li>
	<li><button class="btn btn-link" data-action="?quickfilter=date_asc" id="id_3"  >Oldest</button></li></ul></div>
</div>
EOT;
        return $this->normalizeHTML($expected);
    }
}
