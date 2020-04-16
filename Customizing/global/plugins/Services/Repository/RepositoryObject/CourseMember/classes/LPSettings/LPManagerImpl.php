<?php
namespace CaT\Plugins\CourseMember\LPSettings;

include_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");

/**
 * Implementation of lp manager for ILIAS
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class LPManagerImpl implements LPManager
{
    /**
     * @inheritdoc
     */
    public function refresh($obj_id)
    {
        \ilLPStatusWrapper::_refreshStatus($obj_id);
    }
}
