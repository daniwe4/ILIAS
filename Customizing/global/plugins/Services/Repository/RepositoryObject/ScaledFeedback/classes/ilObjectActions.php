<?php

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback;

use CaT\Plugins\ScaledFeedback\Feedback;
use CaT\Plugins\ScaledFeedback\Settings;

class ilObjectActions
{
    /**
     * @var \ilObjScaledFeedback
     */
    protected $object;

    /**
     * @var Feedback\ilDB
     */
    protected $feedback_db;

    /**
     * @var Settings\ilDB
     */
    protected $settings_db;

    /**
     * @var LPSettings\LPManager
     */
    protected $lp_manager;

    public function __construct(
        \ilObjScaledFeedback $object,
        Feedback\ilDB $feedback_db,
        Settings\ilDB $settings_db,
        LPSettings\LPManager $lp_manager
    ) {
        $this->object = $object;
        $this->feedback_db = $feedback_db;
        $this->settings_db = $settings_db;
        $this->lp_manager = $lp_manager;
    }

    public function create(int $obj_id, int $set_id, int $usr_id, int $dim_id)
    {
        return $this->feedback_db->create($obj_id, $set_id, $usr_id, $dim_id);
    }

    public function updateFeedback(Feedback\Feedback $feedback)
    {
        $this->feedback_db->update($feedback);
    }

    /**
     * @return 	Feedback[]
     */
    public function getAllFeedbacks() : array
    {
        return $this->feedback_db->selectAll();
    }

    /**
     * @return 	Feddback[]
     */
    public function getFeedbacksByIds(int $obj_id, int $set_id)
    {
        return $this->feedback_db->selectByIds($obj_id, $set_id);
    }

    /**
     * Get amount of feedbacks for set
     */
    public function getAmountOfFeedbacks(int $obj_id, int $set_id) : int
    {
        return $this->feedback_db->getAmountOfFeedbacks($obj_id, $set_id);
    }

    public function deleteFeedbackById(int $parent_obj_id)
    {
        $this->feedback_db->delete($parent_obj_id);
    }

    /**
     * @return 	Dimension[]
     */
    public function getDimensionsForSetId(int $set_id) : array
    {
        return $this->feedback_db->getDimensionsForSetId($set_id);
    }

    public function getDimensionTitleById(int $dim_id) : string
    {
        return $this->feedback_db->getDimensionTitleById($dim_id);
    }

    public function getDimensionDisplayedTitleById(int $dim_id) : string
    {
        return $this->feedback_db->getDimensionDisplayedTitleById($dim_id);
    }

    public function checkRepeat(int $obj_id, int $usr_id) : bool
    {
        return $this->feedback_db->checkRepeat($obj_id, $usr_id);
    }

    public function createSettings(int $obj_id)
    {
        return $this->settings_db->create($obj_id);
    }

    public function getSettingsById(int $obj_id) : Settings\Settings
    {
        return $this->settings_db->selectById($obj_id);
    }

    public function updateSettings(Settings\Settings $settings)
    {
        $this->settings_db->update($settings);
    }

    public function deleteSettingById(int $id)
    {
        $this->settings_db->delete($id);
    }

    public function refreshLP()
    {
        $this->lp_manager->refresh((int) $this->getObject()->getId());
    }

    public function getObject() : \ilObjScaledFeedback
    {
        return $this->object;
    }
}
