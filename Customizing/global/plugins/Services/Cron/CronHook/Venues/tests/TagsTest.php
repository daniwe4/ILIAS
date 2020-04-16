<?php
use \CaT\Plugins\Venues\Tags\Tag;
use PHPUnit\Framework\TestCase;

/**
 * Testing the immutable object tag ist really immutable
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class TagsTest extends TestCase
{
    public function test_withName()
    {
        $tag = new Tag(1, "Great", "001122");
        $tag = $tag->withName("Totaly great");

        $this->assertEquals(1, $tag->getId());
        $this->assertEquals("Totaly great", $tag->getName());
        $this->assertEquals("001122", $tag->getColorCode());

        return array($tag);
    }

    /**
     * @depends test_withName
     */
    public function test_withColorCode($tag)
    {
        $tag = $tag[0];
        $tag = $tag->withColorCode("334455");

        $this->assertEquals(1, $tag->getId());
        $this->assertEquals("Totaly great", $tag->getName());
        $this->assertEquals("334455", $tag->getColorCode());
    }
}
