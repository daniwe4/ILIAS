<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Repository/classes/class.ilObjectPlugin.php";
require_once "Services/Tracking/interfaces/interface.ilLPStatusPlugin.php";

use CaT\Plugins\ScaledFeedback;
use CaT\Plugins\ScaledFeedback\ilObjectActions;
use CaT\Plugins\ScaledFeedback\Settings\Settings;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

class ilObjScaledFeedback extends ilObjectPlugin implements ScaledFeedback\ObjScaledFeedback, ilLPStatusPluginInterface
{
    use ilProviderObjectHelper;
    use ScaledFeedback\DI;

    protected static $parent_types = array("crs", "grp");

    /**
     * @var \Pimple\Container
     */
    protected $dic;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var ScaledFeedback\LPSettings\LPManagerImpl
     */
    protected $lp_manager;

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);

        global $DIC;

        $this->dic = $this->getObjectDIC($this, $DIC);
    }

    public function initType()
    {
        $this->setType("xfbk");
    }

    public function doUpdate()
    {
        $this->dic["settings.db"]->update($this->settings);
    }

    public function doRead()
    {
        $id = (int) $this->getId();
        $this->settings = $this->dic["settings.db"]->selectById($id);
    }

    public function doCreate()
    {
        $id = (int) $this->getId();
        $this->settings = $this->dic["settings.db"]->create($id);

        $path = ILIAS_ABSOLUTE_PATH
            . "/Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/classes/SharedUnboundProvider.php";
        $this->createUnboundProvider("crs", CaT\Plugins\ScaledFeedback\SharedUnboundProvider::class, $path);
    }

    /**
     * Get called if the object should be deleted.
     * Delete additional settings
     */
    public function doDelete()
    {
        $id = (int) $this->getId();
        $this->dic["settings.db"]->delete($id);
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be copied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $source = $this->getSettings();
        $function = function ($settings) use ($source) {
            return $settings
                ->withSetId($source->getSetId())
                ->withLPMode($source->getLPMode())
                ->withOnline($source->getOnline())
                ;
        };
        $new_obj->updateSettings($function);
        $new_obj->update();
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    public function getSettings() : Settings
    {
        return $this->settings;
    }

    public function getParentRefId() : int
    {
        return (int) $this->getParent()["ref_id"];
    }

    public function getParentObjId() : int
    {
        return (int) $this->getParent()["obj_id"];
    }

    public function getPluginPath()
    {
        return $this->plugin->getDirectory();
    }

    /**
     * Get the parent object.
     * @throws Exception
     */
    protected function getParent() : array
    {
        global $DIC;
        $tree = $DIC->repositoryTree();

        $ref_id = $this->getRefId();
        if ($ref_id === null) {
            $ref_id = $_GET["ref_id"];
        }
        $parents = $tree->getPathFull($ref_id);
        $parents = array_filter($parents, function ($p) {
            if (in_array($p["type"], self::$parent_types)) {
                return $p;
            }
        });

        if (count($parents) > 0) {
            return array_pop($parents);
        }
        throw new Exception("No parent found!");
    }

    /**
     * @inheritdoc
     */
    public function getLPCompleted() : array
    {
        $set_id = $this->getSettings()->getSetId();
        return $this->dic["feedback.db"]->getUsrIds((int) $this->getId(), $set_id);
    }

    /**
     * @inheritdoc
     */
    public function getLPNotAttempted()
    {
        $parent = $this->getParent();
        $obj = ilObjectFactory::getInstanceByRefId($parent['ref_id']);
        $all_members = $obj->getMembersObject()->getParticipants();
        $lp_completed_members = $this->getLPCompleted();

        return array_diff($all_members, $lp_completed_members);
    }

    /**
     * @inheritdoc
     */
    public function getLPFailed()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getLPInProgress()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getLPStatusForUser($usr_id)
    {
        $set_id = $this->getSettings()->getSetId();
        $usr_ids = $this->dic["feedback.db"]->getUsrIds((int) $this->getId(), $set_id);

        if (in_array($usr_id, $usr_ids)) {
            return ilLPStatus::LP_STATUS_COMPLETED_NUM;
        }
        return ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    public function txtClosure() : Closure
    {
        return function ($code) {
            return $this->txt($code);
        };
    }
}
