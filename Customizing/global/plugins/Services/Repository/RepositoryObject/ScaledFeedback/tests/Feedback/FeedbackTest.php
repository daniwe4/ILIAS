<?php
namespace CaT\Plugins\ScaledFeedback\Feedback;

use CaT\Plugins\ScaledFeedback\Config\Sets\Set;
use CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;
use PHPUnit\Framework\TestCase;

/**
 * Class FeedbackTest
 * Test the Feedback class.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class FeedbackTest extends TestCase
{
    const PARENT_OBJ_ID = 111;
    const PARENT_REF_ID = 222;
    const OBJ_ID = 333;
    const SET_ID = 444;
    const USR_ID = 555;
    const DIM_ID = 666;
    const RATING = 777;
    const COMMENTTEXT = "Ein Test String";

    public function testCreate()
    {
        $object = new Feedback(
            self::OBJ_ID,
            self::SET_ID,
            self::USR_ID,
            self::DIM_ID,
            self::RATING,
            self::COMMENTTEXT,
            self::PARENT_OBJ_ID,
            self::PARENT_REF_ID
            );

        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);
        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);

        return $object;
    }

    /**
     * @depends testCreate
     */
    public function testWithParentObjId($object)
    {
        $newobject = $object->withParentObjId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), 999);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithParentRefId($object)
    {
        $newobject = $object->withParentRefId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), 999);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithObjId($object)
    {
        $newobject = $object->withObjId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), 999);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }
    /**
     * @depends testCreate
     */
    public function testWithSetId($object)
    {
        $newobject = $object->withSetId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), 999);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithUsrId($object)
    {
        $newobject = $object->withUsrId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), 999);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithDimId($object)
    {
        $newobject = $object->withDimId(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), 999);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithRating($object)
    {
        $newobject = $object->withRating(999);

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), 999);
        $this->assertEquals($newobject->getCommenttext(), self::COMMENTTEXT);
    }

    /**
     * @depends testCreate
     */
    public function testWithCommenttext($object)
    {
        $newobject = $object->withCommenttext("999");

        $this->assertEquals($object->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($object->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($object->getObjId(), self::OBJ_ID);
        $this->assertEquals($object->getSetId(), self::SET_ID);
        $this->assertEquals($object->getUsrId(), self::USR_ID);
        $this->assertEquals($object->getDimId(), self::DIM_ID);
        $this->assertEquals($object->getRating(), self::RATING);
        $this->assertEquals($object->getCommenttext(), self::COMMENTTEXT);

        $this->assertEquals($newobject->getParentObjId(), self::PARENT_OBJ_ID);
        $this->assertEquals($newobject->getParentRefId(), self::PARENT_REF_ID);
        $this->assertEquals($newobject->getObjId(), self::OBJ_ID);
        $this->assertEquals($newobject->getSetId(), self::SET_ID);
        $this->assertEquals($newobject->getUsrId(), self::USR_ID);
        $this->assertEquals($newobject->getDimId(), self::DIM_ID);
        $this->assertEquals($newobject->getRating(), self::RATING);
        $this->assertEquals($newobject->getCommenttext(), "999");
    }
}
