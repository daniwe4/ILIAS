<?php
namespace CaT\Plugins\ScaledFeedback\Feedback;

use PHPUnit\Framework\TestCase;

class _ilDB extends ilDB
{
    public function _createTable()
    {
        $this->createTable();
    }
}

/**
 * Class FeedbackTest
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilDBTest extends TestCase
{
    const PARENT_OBJ_ID = 111;
    const PARENT_REF_ID = 222;
    const OBJ_ID = 225;
    const SET_ID = 333;
    const USR_ID = 444;
    const DIM_ID = 555;
    const RATING = 666;
    const COMMENTTEXT = "Teststring";

    public function setUp() : void
    {
        if (!interface_exists("ilDBInterface")) {
            require_once(__DIR__ . "../../ilDBInterface.php");
        }
        $this->db = $this->createMock("\ilDBInterface");
    }

    public function testInstal()
    {
        $this->db
            ->expects($this->once())
            ->method('tableExists')
            ->willReturn(false);

        $this->db
            ->expects($this->once())
            ->method('createTable');

        $ilDB = new _ilDB($this->db);
        $ilDB->_createTable();
    }

    public function testCreate()
    {
        $this->db
            ->expects($this->once())
            ->method('insert');

        $ilDB = new _ilDB($this->db);
        $feedback = $ilDB->create(
            self::OBJ_ID,
            self::SET_ID,
            self::USR_ID,
            self::DIM_ID
        );

        $this->assertEquals(self::OBJ_ID, $feedback->getObjId());
        $this->assertEquals(self::SET_ID, $feedback->getSetId());
        $this->assertEquals(self::USR_ID, $feedback->getUsrId());
        $this->assertEquals(self::DIM_ID, $feedback->getDimId());
    }

    public function testUpdate()
    {
        $feedback = $this->createMock(Feedback::class);

        $feedback
            ->expects($this->once())
            ->method('getParentObjId')
            ->willReturn(3);
        $feedback
            ->expects($this->once())
            ->method('getParentRefId');
        $feedback
            ->expects($this->once())
            ->method('getObjId');
        $feedback
            ->expects($this->once())
            ->method('getSetId');
        $feedback
            ->expects($this->once())
            ->method('getUsrId');
        $feedback
            ->expects($this->once())
            ->method('getDimId');
        $feedback
            ->expects($this->once())
            ->method('getRating');
        $feedback
            ->expects($this->once())
            ->method('getCommenttext');

        $this->db
            ->expects($this->once())
            ->method('update');

        $ilDB = new _ilDB($this->db);
        $ilDB->update($feedback);
    }

    public function testSelectAll()
    {
        $this->db
            ->expects($this->once())
            ->method('query');

        $ilDB = new _ilDB($this->db);
        $ilDB->selectAll();
    }

    public function testSelectById()
    {
        $this->db
            ->expects($this->exactly(2))
            ->method('quote');
        $this->db
            ->expects($this->once())
            ->method('query');
        $this->db
            ->expects($this->once())
            ->method('numRows')
            ->willReturn(1);
        $this->db
            ->expects($this->any())
            ->method('fetchAssoc')
            ->will($this->onConsecutiveCalls(
                array(
                    "parent_obj_id" => self::PARENT_OBJ_ID,
                    "parent_ref_id" => self::PARENT_REF_ID,
                    "obj_id" => self::OBJ_ID,
                    "set_id" => self::SET_ID,
                    "usr_id" => self::USR_ID,
                    "dim_id" => self::DIM_ID,
                    "rating" => self::RATING,
                    "commenttext" => self::COMMENTTEXT
                ),
                0
            ));

        $ilDB = new _ilDB($this->db);
        $feedback = $ilDB->selectByIds(self::OBJ_ID, 33);

        $this->assertEquals(self::PARENT_OBJ_ID, $feedback[0]->getParentObjId());
        $this->assertEquals(self::PARENT_REF_ID, $feedback[0]->getParentRefId());
        $this->assertEquals(self::OBJ_ID, $feedback[0]->getObjId());
        $this->assertEquals(self::SET_ID, $feedback[0]->getSetId());
        $this->assertEquals(self::USR_ID, $feedback[0]->getUsrId());
        $this->assertEquals(self::DIM_ID, $feedback[0]->getDimId());
        $this->assertEquals(self::RATING, $feedback[0]->getRating());
        $this->assertEquals(self::COMMENTTEXT, $feedback[0]->getCommenttext());
    }

    public function testDelete()
    {
        $this->db
            ->expects($this->once())
            ->method('manipulate');

        $ilDB = new _ilDB($this->db);
        $ilDB->delete(12);
    }
}
