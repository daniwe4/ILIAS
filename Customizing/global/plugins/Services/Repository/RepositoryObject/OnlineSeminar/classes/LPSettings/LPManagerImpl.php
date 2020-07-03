<?php
namespace CaT\Plugins\OnlineSeminar\LPSettings;

include_once("Services/Tracking/classes/class.ilLPStatusWrapper.php");

class LPManagerImpl implements LPManager
{
    /**
     * @inheritdoc
     */
    public function refresh($obj_id)
    {
        \ilLPStatusWrapper::_refreshStatus($obj_id);
    }

    /**
     * @inheritdoc
     */
    public function coursePassed($needed, array $lp_data)
    {
        if ($lp_data["passed"] === null && $lp_data["minutes"] >= $needed) {
            return true;
        } else {
            if ((bool) $lp_data["passed"]) {
                return true;
            }
        }

        return false;
    }
}
