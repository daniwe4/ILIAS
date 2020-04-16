<?php
namespace CaT\Plugins\ScaledFeedback\Config;

use PHPUnit\Framework\TestCase;
use CaT\Plugins\ScaledFeedback\Config\Sets\Set;
use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

/**
 * Wrapper class for testing protected methods.
 */
class _ilDB extends ilDB
{
    public function _createSetsTable()
    {
        $this->createSetsTable();
    }

    public function _createDimensionsTable()
    {
        $this->createDimensionsTable();
    }

    public function _createInterimTable()
    {
        $this->createInterimTable();
    }
}

/**
 * Class Dimension\ilDBTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilDBTest extends TestCase
{
    const DIM_ID = 22;
    const TITLE = "title";
    const DISPLAYED_TITLE = "displayed_title";
    const INFO = "info";
    const LABEL1 = "label1";
    const LABEL2 = "label2";
    const LABEL3 = "label3";
    const LABEL4 = "label4";
    const LABEL5 = "label5";
    const ENABLE_COMMENT = true;
    const IS_LOCKED = true;
    const ONLY_TEXTUAL_FEEDBACK = true;
    const SET_ID = 22;
    const INTROTEXT = "introtext";
    const EXTROTEXT = "extrotext";
    const REPEATTEXT = "repeattext";
    const MIN_SUBMISSIONS = 6;
    const ORDERNUMBER = 10;
    const IS_USED = true;

    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "/../ilDBInterface.php");
        }
        $this->db = $this->createMock("\ilDBInterface");
    }

    /**********************************************************************
     *								Test Sets
     **********************************************************************/
    public function testCreateSet()
    {
        $this->db
            ->expects($this->once())
            ->method("nextId")
            ->with(ilDB::TABLENAME_SETS)
            ->will($this->returnCallback(function ($i) {
                return is_string($i);
            }));
        $this->db
            ->expects($this->once())
            ->method('insert');

        $ilDB = new _ilDB($this->db);
        $set = $ilDB->createSet(
            self::TITLE,
            self::IS_LOCKED,
            self::MIN_SUBMISSIONS
        );

        $this->assertEquals(self::TITLE, $set->getTitle());
        $this->assertEquals(self::IS_LOCKED, $set->getIsLocked());
        $this->assertEquals(self::MIN_SUBMISSIONS, $set->getMinSubmissions());
    }

    public function testUpdateSet()
    {
        $set = $this->createMock(Set::class);

        $set
            ->expects($this->once())
            ->method('getSetId')
            ->willReturn(self::SET_ID);
        $set
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn(self::TITLE);
        $set
            ->expects($this->once())
            ->method('getIntrotext')
            ->willReturn(self::INTROTEXT);
        $set
            ->expects($this->once())
            ->method('getExtrotext')
            ->willReturn(self::EXTROTEXT);
        $set
            ->expects($this->once())
            ->method('getRepeattext')
            ->willReturn(self::REPEATTEXT);
        $set
            ->expects($this->once())
            ->method('getIsLocked')
            ->willReturn(self::IS_LOCKED);
        $set
            ->expects($this->once())
            ->method('getMinSubmissions')
            ->willReturn(self::MIN_SUBMISSIONS);
        $set
            ->expects($this->once())
            ->method('getDimensions')
            ->willReturn(array(new Dimension(
                self::DIM_ID,
                self::TITLE,
                self::DISPLAYED_TITLE,
                self::INFO,
                self::LABEL1,
                self::LABEL2,
                self::LABEL3,
                self::LABEL4,
                self::LABEL5,
                self::ENABLE_COMMENT,
                self::ONLY_TEXTUAL_FEEDBACK,
                self::IS_LOCKED
            )));

        $where = ["set_id" => ["integer", self::SET_ID]];
        $values = array(
            "title" => ["text", self::TITLE],
            "introtext" => ["text", self::INTROTEXT],
            "extrotext" => ["text", self::EXTROTEXT],
            "repeattext" => ["text", self::REPEATTEXT],
            "is_locked" => ["integer", self::IS_LOCKED],
            "min_submissions" => ["integer", self::MIN_SUBMISSIONS]
            );

        $this->db
            ->expects($this->any())
            ->method('quote')
            ->with(self::SET_ID, "integer")
            ->willReturn(self::SET_ID);
        $this->db
            ->expects($this->once())
            ->method('update')
            ->with(ilDB::TABLENAME_SETS, $values, $where);

        $ilDB = new _ilDB($this->db);
        $ilDB->updateSet($set);
    }

    public function testSelectAllSets()
    {
        $query1 = "SELECT" . PHP_EOL
                . "    set_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    introtext," . PHP_EOL
                . "    extrotext," . PHP_EOL
                . "    repeattext," . PHP_EOL
                . "    is_locked," . PHP_EOL
                . "    min_submissions" . PHP_EOL
                . "FROM " . ilDB::TABLENAME_SETS . PHP_EOL
                . "ORDER BY title ASC";

        $query2 = "SELECT" . PHP_EOL
                . "    interim.ordernumber," . PHP_EOL
                . "    dim.dim_id," . PHP_EOL
                . "    dim.title," . PHP_EOL
                . "    dim.displayed_title," . PHP_EOL
                . "    dim.info," . PHP_EOL
                . "    dim.label1," . PHP_EOL
                . "    dim.label2," . PHP_EOL
                . "    dim.label3," . PHP_EOL
                . "    dim.label4," . PHP_EOL
                . "    dim.label5," . PHP_EOL
                . "    dim.enable_comment," . PHP_EOL
                . "    dim.only_textual_feedback," . PHP_EOL
                . "    dim.is_locked" . PHP_EOL
                . "FROM " . ilDB::INTERIM_TABLE . " AS interim" . PHP_EOL
                . "JOIN " . ilDB::TABLENAME_DIMS . " AS dim" . PHP_EOL
                . "    ON dim.dim_id = interim.dim_id" . PHP_EOL
                . "WHERE interim.set_id = " . self::SET_ID . PHP_EOL
                . "ORDER BY interim.ordernumber ASC" . PHP_EOL;

        $this->db
            ->expects($this->any())
            ->method('query')
            ->withConsecutive([$query1], [$query2]);
        $this->db
            ->expects($this->any())
            ->method('fetchAssoc')
            ->will(
                $this->onConsecutiveCalls(
                    array(
                    "set_id" => self::SET_ID,
                    "title" => self::TITLE,
                    "introtext" => self::INTROTEXT,
                    "extrotext" => self::EXTROTEXT,
                    "repeattext" => self::REPEATTEXT,
                    "is_locked" => self::IS_LOCKED,
                    "min_submissions" => self::MIN_SUBMISSIONS
                ),
                    array(
                    "ordernumber" => self::ORDERNUMBER,
                    "dim_id" => self::DIM_ID,
                    "title" => self::TITLE,
                    "displayed_title" => self::DISPLAYED_TITLE,
                    "info" => self::INFO,
                    "label1" => self::LABEL1,
                    "label2" => self::LABEL2,
                    "label3" => self::LABEL3,
                    "label4" => self::LABEL4,
                    "label5" => self::LABEL5,
                    "enable_comment" => self::ENABLE_COMMENT,
                    "only_textual_feedback" => self::ONLY_TEXTUAL_FEEDBACK,
                    "is_locked" => self::IS_LOCKED
                ),
                    null,
                    null
                )
            );
        $this->db
            ->expects($this->any())
            ->method('quote')
            ->willReturn(22);

        $ilDB = new _ilDB($this->db);
        $set = $ilDB->selectAllSets()[0];

        $this->assertEquals(self::SET_ID, $set->getSetId());
        $this->assertEquals(self::TITLE, $set->getTitle());
        $this->assertEquals(self::INTROTEXT, $set->getIntrotext());
        $this->assertEquals(self::EXTROTEXT, $set->getExtrotext());
        $this->assertEquals(self::REPEATTEXT, $set->getRepeattext());
        $this->assertEquals(self::IS_LOCKED, $set->getIsLocked());
        $this->assertEquals(self::MIN_SUBMISSIONS, $set->getMinSubmissions());

        foreach ($set->getDimensions() as $dim) {
            $this->assertEquals(self::ORDERNUMBER, $dim->getOrdernumber());
            $this->assertEquals(self::DIM_ID, $dim->getDimId());
            $this->assertEquals(self::TITLE, $dim->getTitle());
            $this->assertEquals(self::DISPLAYED_TITLE, $dim->getDisplayedTitle());
            $this->assertEquals(self::INFO, $dim->getInfo());
            $this->assertEquals(self::LABEL1, $dim->getLabel1());
            $this->assertEquals(self::LABEL2, $dim->getLabel2());
            $this->assertEquals(self::LABEL3, $dim->getLabel3());
            $this->assertEquals(self::LABEL4, $dim->getLabel4());
            $this->assertEquals(self::LABEL5, $dim->getLabel5());
            $this->assertEquals(self::ENABLE_COMMENT, $dim->getEnableComment());
            $this->assertEquals(self::ONLY_TEXTUAL_FEEDBACK, $dim->getOnlyTextualFeedback());
            $this->assertEquals(self::IS_LOCKED, $dim->getIsLocked());
        }
    }

    public function testSelectSetById()
    {
        $this->db
            ->expects($this->any())
            ->method('quote')
            ->with(self::SET_ID, "integer")
            ->willReturn(self::SET_ID);
        $this->db
            ->expects($this->any())
            ->method('query');
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1);
        $this->db
            ->expects($this->exactly(5))
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls(
                array(
                    "set_id" => self::SET_ID,
                    "title" => self::TITLE,
                    "introtext" => self::INTROTEXT,
                    "extrotext" => self::EXTROTEXT,
                    "repeattext" => self::REPEATTEXT,
                    "is_locked" => self::IS_LOCKED,
                    "min_submissions" => self::MIN_SUBMISSIONS
                ),
                array(
                    "ordernumber" => self::ORDERNUMBER,
                    "dim_id" => self::DIM_ID,
                    "title" => self::TITLE,
                    "displayed_title" => self::DISPLAYED_TITLE,
                    "info" => self::INFO,
                    "label1" => self::LABEL1,
                    "label2" => self::LABEL2,
                    "label3" => self::LABEL3,
                    "label4" => self::LABEL4,
                    "label5" => self::LABEL5,
                    "enable_comment" => self::ENABLE_COMMENT,
                    "only_textual_feedback" => self::ONLY_TEXTUAL_FEEDBACK,
                    "is_locked" => self::IS_LOCKED
                ),
                null
            ));

        $ilDB = new _ilDB($this->db);
        $set = $ilDB->selectSetById(self::SET_ID);

        $this->assertEquals(self::SET_ID, $set->getSetId());
        $this->assertEquals(self::TITLE, $set->getTitle());
        $this->assertEquals(self::INTROTEXT, $set->getIntrotext());
        $this->assertEquals(self::EXTROTEXT, $set->getExtrotext());
        $this->assertEquals(self::REPEATTEXT, $set->getRepeattext());
        $this->assertEquals(self::IS_LOCKED, $set->getIsLocked());
        $this->assertEquals(self::MIN_SUBMISSIONS, $set->getMinSubmissions());

        foreach ($set->getDimensions() as $dim) {
            $this->assertEquals(self::ORDERNUMBER, $dim->getOrdernumber());
            $this->assertEquals(self::DIM_ID, $dim->getDimId());
            $this->assertEquals(self::TITLE, $dim->getTitle());
            $this->assertEquals(self::DISPLAYED_TITLE, $dim->getDisplayedTitle());
            $this->assertEquals(self::INFO, $dim->getInfo());
            $this->assertEquals(self::LABEL1, $dim->getLabel1());
            $this->assertEquals(self::LABEL2, $dim->getLabel2());
            $this->assertEquals(self::LABEL3, $dim->getLabel3());
            $this->assertEquals(self::LABEL4, $dim->getLabel4());
            $this->assertEquals(self::LABEL5, $dim->getLabel5());
            $this->assertEquals(self::ENABLE_COMMENT, $dim->getEnableComment());
            $this->assertEquals(self::ONLY_TEXTUAL_FEEDBACK, $dim->getOnlyTextualFeedback());
            $this->assertEquals(self::IS_LOCKED, $dim->getIsLocked());
        }
    }

    public function testDeleteSet()
    {
        $query1 = "DELETE FROM " . ilDB::TABLENAME_SETS . PHP_EOL
                 . "WHERE set_id = " . self::SET_ID;

        $query2 = "DELETE FROM " . ilDB::INTERIM_TABLE . PHP_EOL
                 . "WHERE set_id = " . self::SET_ID;

        $this->db
            ->expects($this->exactly(2))
            ->method('quote')
            ->with(self::SET_ID, "integer")
            ->willReturn(self::SET_ID);
        $this->db
            ->expects($this->exactly(2))
            ->method('manipulate')
            ->will($this->onConsecutiveCalls([$query1], [$query2]));

        $ilDB = new _ilDB($this->db);
        $ilDB->deleteSets(array(self::SET_ID));
    }

    public function testSetsInstall()
    {
        $fields = array(
            'set_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                ),
            'title' => array(
                'type' => 'text',
                'length' => 255
                ),
            'introtext' => array(
                'type' => 'text',
                'length' => 255
                ),
            'extrotext' => array(
                'type' => 'text',
                'length' => 255
                ),
            'repeattext' => array(
                'type' => 'text',
                'length' => 255
                ),
            'is_locked' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
                ),
            'min_submissions' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                )
            );

        $this->db
            ->expects($this->once())
            ->method('tableExists')
            ->with(ilDB::TABLENAME_SETS)
            ->willReturn(false);
        $this->db
            ->expects($this->once())
            ->method('createTable')
            ->with(ilDB::TABLENAME_SETS, $fields);

        $ilDB = new _ilDB($this->db);
        $ilDB->_createSetsTable();
    }

    /**********************************************************************
     *							Test Dimensions
     **********************************************************************/
    public function testCreateDimension()
    {
        $this->db
            ->expects($this->once())
            ->method("nextId")
            ->with(ilDB::TABLENAME_DIMS)
            ->will($this->returnCallback(function ($i) {
                return is_string($i);
            }))
            ->willReturn(self::DIM_ID);
        $this->db
            ->expects($this->once())
            ->method('insert');

        $ilDB = new _ilDB($this->db);
        $dimension = $ilDB->createDimension(
            self::TITLE,
            self::DISPLAYED_TITLE
        );

        $this->assertEquals(self::DIM_ID, $dimension->getDimId());
        $this->assertEquals(self::TITLE, $dimension->getTitle());
    }

    public function testUpdateDimension()
    {
        $dimension = $this->createMock(Dimension::class);

        $dimension
            ->expects($this->once())
            ->method('getDimId')
            ->willReturn(self::DIM_ID);
        $dimension
            ->expects($this->once())
            ->method('getTitle')
            ->willReturn(self::TITLE);
        $dimension
            ->expects($this->once())
            ->method('getDisplayedTitle')
            ->willReturn(self::DISPLAYED_TITLE);
        $dimension
            ->expects($this->once())
            ->method('getInfo')
            ->willReturn(self::INFO);
        $dimension
            ->expects($this->once())
            ->method('getLabel1')
            ->willReturn(self::LABEL1);
        $dimension
            ->expects($this->once())
            ->method('getLabel2')
            ->willReturn(self::LABEL2);
        $dimension
            ->expects($this->once())
            ->method('getLabel3')
            ->willReturn(self::LABEL3);
        $dimension
            ->expects($this->once())
            ->method('getLabel4')
            ->willReturn(self::LABEL4);
        $dimension
            ->expects($this->once())
            ->method('getLabel5')
            ->willReturn(self::LABEL5);
        $dimension
            ->expects($this->once())
            ->method('getEnableComment')
            ->willReturn(self::ENABLE_COMMENT);
        $dimension
            ->expects($this->once())
            ->method('getOnlyTextualFeedback')
            ->willReturn(self::ONLY_TEXTUAL_FEEDBACK);
        $dimension
            ->expects($this->once())
            ->method('getIsLocked')
            ->willReturn(self::IS_LOCKED);

        $where = ["dim_id" => ["integer", self::DIM_ID]];
        $values = array(
            "title" => ["text", self::TITLE],
            "displayed_title" => ["text", self::DISPLAYED_TITLE],
            "info" => ["text", self::INFO],
            "label1" => ["text", self::LABEL1],
            "label2" => ["text", self::LABEL2],
            "label3" => ["text", self::LABEL3],
            "label4" => ["text", self::LABEL4],
            "label5" => ["text", self::LABEL5],
            "enable_comment" => ["integer", self::ENABLE_COMMENT],
            "only_textual_feedback" => ["integer", self::ONLY_TEXTUAL_FEEDBACK],
            "is_locked" => ["integer", self::IS_LOCKED]
            );

        $this->db
            ->expects($this->once())
            ->method('update')
            ->with(ilDB::TABLENAME_DIMS, $values, $where);

        $ilDB = new _ilDB($this->db);
        $ilDB->updateDimension($dimension);
    }

    public function testSelectAllDimensions()
    {
        $query1 = "SELECT" . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    displayed_title," . PHP_EOL
                . "    info," . PHP_EOL
                . "    label1," . PHP_EOL
                . "    label2," . PHP_EOL
                . "    label3," . PHP_EOL
                . "    label4," . PHP_EOL
                . "    label5," . PHP_EOL
                . "    enable_comment," . PHP_EOL
                . "    only_textual_feedback," . PHP_EOL
                . "    is_locked" . PHP_EOL
                . "FROM " . ilDB::TABLENAME_DIMS . PHP_EOL
                  . "" . PHP_EOL
                . "ORDER BY title ASC";

        $query2 = "SELECT count(dim_id) AS dim" . PHP_EOL
                 . "FROM " . ilDB::INTERIM_TABLE . PHP_EOL
                 . "WHERE dim_id = " . self::DIM_ID;
        $this->db
            ->expects($this->any())
            ->method('quote')
            ->willReturn(22);
        $this->db
            ->expects($this->any())
            ->method('query')
            ->withConsecutive([$query1], [$query2]);
        $this->db
            ->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->will(
                $this->onConsecutiveCalls(
                    array(
                    "dim_id" => self::DIM_ID,
                    "title" => self::TITLE,
                    "displayed_title" => self::DISPLAYED_TITLE,
                    "info" => self::INFO,
                    "label1" => self::LABEL1,
                    "label2" => self::LABEL2,
                    "label3" => self::LABEL3,
                    "label4" => self::LABEL4,
                    "label5" => self::LABEL5,
                    "enable_comment" => self::ENABLE_COMMENT,
                    "only_textual_feedback" => self::ONLY_TEXTUAL_FEEDBACK,
                    "is_locked" => self::IS_LOCKED
                    ),
                    array(
                    "dim" => 1
                    ),
                    null
                )
            );

        $ilDB = new _ilDB($this->db);
        $dimension = $ilDB->selectAllDimensions()[0];

        $this->assertEquals(self::DIM_ID, $dimension->getDimId());
        $this->assertEquals(self::TITLE, $dimension->getTitle());
        $this->assertEquals(self::DISPLAYED_TITLE, $dimension->getDisplayedTitle());
        $this->assertEquals(self::INFO, $dimension->getInfo());
        $this->assertEquals(self::LABEL1, $dimension->getLabel1());
        $this->assertEquals(self::LABEL2, $dimension->getLabel2());
        $this->assertEquals(self::LABEL3, $dimension->getLabel3());
        $this->assertEquals(self::LABEL4, $dimension->getLabel4());
        $this->assertEquals(self::LABEL5, $dimension->getLabel5());
        $this->assertEquals(self::ENABLE_COMMENT, $dimension->getEnableComment());
        $this->assertEquals(self::ONLY_TEXTUAL_FEEDBACK, $dimension->getOnlyTextualFeedback());
        $this->assertEquals(self::IS_LOCKED, $dimension->getIsLocked());
    }

    public function testSelectDimensionById()
    {
        $query1 = "SELECT" . PHP_EOL
                . "    dim_id," . PHP_EOL
                . "    title," . PHP_EOL
                . "    displayed_title," . PHP_EOL
                . "    info," . PHP_EOL
                . "    label1," . PHP_EOL
                . "    label2," . PHP_EOL
                . "    label3," . PHP_EOL
                . "    label4," . PHP_EOL
                . "    label5," . PHP_EOL
                . "    enable_comment," . PHP_EOL
                . "    only_textual_feedback," . PHP_EOL
                . "    is_locked" . PHP_EOL
                . "FROM " . ilDB::TABLENAME_DIMS . PHP_EOL
                . "WHERE dim_id = " . self::DIM_ID;

        $query2 = "SELECT count(dim_id) AS dim" . PHP_EOL
                 . "FROM " . ilDB::INTERIM_TABLE . PHP_EOL
                 . "WHERE dim_id = " . self::DIM_ID;

        $this->db
            ->expects($this->any())
            ->method('quote')
            ->with(self::DIM_ID, "integer")
            ->willReturn(self::DIM_ID);
        $this->db
            ->expects($this->any())
            ->method('query')
            ->withConsecutive([$query1], [$query2]);
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1);
        $this->db
            ->expects($this->exactly(2))
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls(
                array(
                    "dim_id" => self::DIM_ID,
                    "title" => self::TITLE,
                    "displayed_title" => self::DISPLAYED_TITLE,
                    "info" => self::INFO,
                    "label1" => self::LABEL1,
                    "label2" => self::LABEL2,
                    "label3" => self::LABEL3,
                    "label4" => self::LABEL4,
                    "label5" => self::LABEL5,
                    "enable_comment" => self::ENABLE_COMMENT,
                    "only_textual_feedback" => self::ONLY_TEXTUAL_FEEDBACK,
                    "is_locked" => self::IS_LOCKED
                    ),
                array(
                    "dim" => 1
                    )
            ));

        $ilDB = new _ilDB($this->db);
        $dimension = $ilDB->selectDimensionById(self::DIM_ID);

        $this->assertEquals(self::DIM_ID, $dimension->getDimId());
        $this->assertEquals(self::TITLE, $dimension->getTitle());
        $this->assertEquals(self::DISPLAYED_TITLE, $dimension->getDisplayedTitle());
        $this->assertEquals(self::INFO, $dimension->getInfo());
        $this->assertEquals(self::LABEL1, $dimension->getLabel1());
        $this->assertEquals(self::LABEL2, $dimension->getLabel2());
        $this->assertEquals(self::LABEL3, $dimension->getLabel3());
        $this->assertEquals(self::LABEL4, $dimension->getLabel4());
        $this->assertEquals(self::LABEL5, $dimension->getLabel5());
        $this->assertEquals(self::ENABLE_COMMENT, $dimension->getEnableComment());
        $this->assertEquals(self::ONLY_TEXTUAL_FEEDBACK, $dimension->getOnlyTextualFeedback());
        $this->assertEquals(self::IS_LOCKED, $dimension->getIsLocked());
        $this->assertEquals(self::IS_USED, $dimension->getIsUsed());
    }

    public function testDeleteDimensions()
    {
        $query = "DELETE FROM " . ilDB::TABLENAME_DIMS . PHP_EOL
                . "WHERE dim_id = " . self::DIM_ID;

        $this->db
            ->expects($this->once())
            ->method('quote')
            ->with(self::DIM_ID, "integer")
            ->willReturn(self::DIM_ID);
        $this->db
            ->expects($this->once())
            ->method('manipulate')
            ->with($query);

        $ilDB = new _ilDB($this->db);
        $ilDB->deleteDimensions(array(self::DIM_ID));
    }

    public function testIsValidDimId()
    {
        $query = "SELECT dim_id" . PHP_EOL
                . "FROM " . ilDB::TABLENAME_DIMS . PHP_EOL
                . "WHERE dim_id = " . self::DIM_ID;

        $result = array("1");
        $this->db
            ->expects($this->once())
            ->method('query')
            ->with($query)
            ->willReturn($result);
        $this->db
            ->expects($this->once())
            ->method("quote")
            ->willReturn(self::DIM_ID);
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->with($result)
            ->willReturn(1);

        $ilDB = new _ilDB($this->db);
        $is_valid = $ilDB->isValidDimId(self::DIM_ID);

        $this->assertTrue($is_valid);
    }

    public function testDimensionsInstall()
    {
        $fields = array(
            'dim_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                ),
            'title' => array(
                'type' => 'text',
                'length' => 255
                ),
            'info' => array(
                'type' => 'text',
                'length' => 255
                ),
            'label1' => array(
                'type' => 'text',
                'length' => 255
                ),
            'label2' => array(
                'type' => 'text',
                'length' => 255
                ),
            'label3' => array(
                'type' => 'text',
                'length' => 255
                ),
            'label4' => array(
                'type' => 'text',
                'length' => 255
                ),
            'label5' => array(
                'type' => 'text',
                'length' => 255
                ),
            'enable_comment' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
                ),
            'is_locked' => array(
                'type' => 'integer',
                'length' => 1,
                'notnull' => true
                )
            );

        $this->db
            ->expects($this->once())
            ->method('tableExists')
            ->with(ilDB::TABLENAME_DIMS)
            ->willReturn(false);
        $this->db
            ->expects($this->once())
            ->method('createTable')
            ->with(ilDB::TABLENAME_DIMS, $fields);

        $ilDB = new _ilDB($this->db);
        $ilDB->_createDimensionsTable();
    }

    /**********************************************************************
     *							Test Interim
     **********************************************************************/
    public function testInterimInstall()
    {
        $fields = array(
            'set_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                ),
            'dim_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                ),
            'ordernumber' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
                )
            );

        $this->db
            ->expects($this->once())
            ->method('tableExists')
            ->with(ilDB::INTERIM_TABLE)
            ->willReturn(false);
        $this->db
            ->expects($this->once())
            ->method('createTable')
            ->with(ilDB::INTERIM_TABLE, $fields);

        $ilDB = new _ilDB($this->db);
        $ilDB->_createInterimTable();
    }
}
