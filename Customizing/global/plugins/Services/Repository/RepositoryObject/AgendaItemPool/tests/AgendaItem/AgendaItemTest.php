<?php
namespace CaT\Plugins\AgendaItemPool\AgendaItem;

use PHPUnit\Framework\TestCase;

/**
 * Class AgendaItemTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class AgendaItemTest extends TestCase
{
    protected $test_int;
    protected $test_string;
    protected $test_date;

    public function setUp() : void
    {
        $this->test_int = 33;
        $this->test_arr = [22, 33, 44];
        $this->test_string = "testobject";
        $this->test_date = \DateTime::createFromFormat("Y-m-d H:i:s", "2018-02-02 12:01:15");
    }

    public function testCreate()
    {
        $object = new AgendaItem(
            $this->test_int,
            $this->test_string,
            $this->test_string,
            false,
            false,
            false,
            $this->test_date,
            $this->test_int,
            10,
            false,
            array(),
            "",
            "",
            "",
            ""
        );

        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithTitle($object)
    {
        $newObject = $object->withTitle("ABCDEF");
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), "ABCDEF");
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithTitle
     */
    public function testWithDescription($object)
    {
        $newObject = $object->withDescription("ABCDEF");
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), "ABCDEF");
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithDescription
     */
    public function testWithIsActive($object)
    {
        $newObject = $object->withIsActive(true);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), true);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithIsActive
     */
    public function testWithIddRelevant($object)
    {
        $newObject = $object->withIddRelevant(true);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), true);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithIddRelevant
     */
    public function testWithIsDeleted($object)
    {
        $newObject = $object->withIsDeleted(true);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), true);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithIsDeleted
     */
    public function testWithLastChange($object)
    {
        $date = new \DateTime("2017-01-05 15:42:00");
        $newObject = $object->withLastChange($date);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithLastChange
     */
    public function testWithChangeUsrId($object)
    {
        $date = new \DateTime("2017-01-05 15:42:00");
        $newObject = $object->withChangeUsrId(22);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), 22);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithChangeUsrId
     */
    public function testWithPoolId($object)
    {
        $newObject = $object->withPoolId(22);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 22);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithPoolId
     */
    public function testWithIsBlank($object)
    {
        $newObject = $object->withIsBlank(true);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), true);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithIsBlank
     */
    public function testWithTrainingTopics($object)
    {
        $newObject = $object->withTrainingTopics($this->test_arr);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), false);
        $this->assertEquals($newObject->getTrainingTopics(), $this->test_arr);
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithTrainingTopics
     */
    public function testWithGoals($object)
    {
        $newObject = $object->withGoals($this->test_string);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), false);
        $this->assertEquals($newObject->getTrainingTopics(), array());
        $this->assertEquals($newObject->getGoals(), $this->test_string);
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithGoals
     */
    public function testWithGDVLearningContent($object)
    {
        $newObject = $object->withGDVLearningContent($this->test_string);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), false);
        $this->assertEquals($newObject->getTrainingTopics(), array());
        $this->assertEquals($newObject->getGoals(), "");
        $this->assertEquals($newObject->getGDVLearningContent(), $this->test_string);
        $this->assertEquals($newObject->getIDDLearningContent(), "");
        $this->assertEquals($newObject->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithGDVLearningContent
     */
    public function testWithIDDLearningContent($object)
    {
        $newObject = $object->withIDDLearningContent($this->test_string);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), false);
        $this->assertEquals($newObject->getTrainingTopics(), array());
        $this->assertEquals($newObject->getGoals(), "");
        $this->assertEquals($newObject->getGDVLearningContent(), "");
        $this->assertEquals($newObject->getIDDLearningContent(), $this->test_string);
        $this->assertEquals($newObject->getAgendaItemContent(), "");

        return $object;
    }

    /**
     * @depends testWithIDDLearningContent
     */
    public function testWithUseTrainingContent($object)
    {
        $newObject = $object->withAgendaItemContent($this->test_string);
        $this->assertEquals($object->getObjId(), $this->test_int);
        $this->assertEquals($object->getTitle(), $this->test_string);
        $this->assertEquals($object->getDescription(), $this->test_string);
        $this->assertEquals($object->getIsActive(), false);
        $this->assertEquals($object->getIddRelevant(), false);
        $this->assertEquals($object->getIsDeleted(), false);
        $this->assertEquals($object->getLastChange(), $this->test_date);
        $this->assertEquals($object->getChangeUsrId(), $this->test_int);
        $this->assertEquals($object->getPoolId(), 10);
        $this->assertEquals($object->getTrainingTopics(), array());
        $this->assertEquals($object->getGoals(), "");
        $this->assertEquals($object->getGDVLearningContent(), "");
        $this->assertEquals($object->getIDDLearningContent(), "");
        $this->assertEquals($object->getAgendaItemContent(), "");

        $this->assertEquals($newObject->getObjId(), $this->test_int);
        $this->assertEquals($newObject->getTitle(), $this->test_string);
        $this->assertEquals($newObject->getDescription(), $this->test_string);
        $this->assertEquals($newObject->getIsActive(), false);
        $this->assertEquals($newObject->getIddRelevant(), false);
        $this->assertEquals($newObject->getIsDeleted(), false);
        $this->assertEquals($newObject->getLastChange(), $this->test_date);
        $this->assertEquals($newObject->getChangeUsrId(), $this->test_int);
        $this->assertEquals($newObject->getPoolId(), 10);
        $this->assertEquals($newObject->getIsBlank(), false);
        $this->assertEquals($newObject->getTrainingTopics(), array());
        $this->assertEquals($newObject->getGoals(), "");
        $this->assertEquals($newObject->getGDVLearningContent(), "");
        $this->assertEquals($newObject->getIDDLearningContent(), "");
        $this->assertEquals($newObject->getAgendaItemContent(), $this->test_string);
    }
}
