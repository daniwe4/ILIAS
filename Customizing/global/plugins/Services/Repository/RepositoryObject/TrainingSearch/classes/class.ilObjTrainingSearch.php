<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\TrainingSearch;

require_once "Services/Repository/classes/class.ilObjectPlugin.php";

/**
 * Object of the plugin
 */
class ilObjTrainingSearch extends ilObjectPlugin
{
    use TrainingSearch\DI;

    const REPOSITORY_REF_ID = 1;
    const ORGU_BOOK_USER = "orgu_book_user";
    const ORGU_VIEW_BOOKINGS = "orgu_view_bookings";

    public function __construct($a_ref_id = 0)
    {
        parent::__construct($a_ref_id);
        $this->settings_actions = null;
        $this->settings_db = null;
    }

    /**
     * @inheritdoc
     */
    public function initType()
    {
        $this->setType("xtrs");
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->getSettingsDB()->create((int) $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function doUpdate()
    {
        $this->getSettingsDB()->update($this->settings);
    }

    /**
     * @inheritdoc
     */
    public function doRead()
    {
        $this->settings = $this->getSettingsDB()->select((int) $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function doDelete()
    {
        $this->getSettingsDB()->delete((int) $this->getId());
    }

    /**
     * @inheritdoc
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $new_obj->doRead();
        $new_obj->updateSettings(function (TrainingSearch\Settings\Settings $s) {
            $settings = $this->getSettings();
            return $s->withIsOnline($settings->getIsOnline())
                ->withIsLocal($settings->isLocal())
                ->withIsRecommendationAllowed($settings->isRecommendationAllowed())
                ->withRelevantCategories($settings->relevantCategories())
                ->withRelevantTargetGroups($settings->relevantTargetGroups())
                ->withRelevantTopics($settings->relevantTopics())
            ;
        });

        $new_obj->update();
    }

    protected function getSettingsDB()
    {
        return $this->getDI()["settings.db"];
    }

    public function getSettings() : TrainingSearch\Settings\Settings
    {
        return $this->settings;
    }

    public function updateSettings(\Closure $fnc)
    {
        $this->settings = $fnc($this->settings);
    }

    public function getDI()
    {
        if (is_null($this->di)) {
            global $DIC;
            $this->di = $this->getObjectDI($this, $DIC);
        }

        return $this->di;
    }

    public function getTxtClosure() : Closure
    {
        return function ($code) {
            return $this->plugin->txt($code);
        };
    }

    public function getPositionWithBookPermission()
    {
        $op = $this->getOperationIdFor(self::ORGU_BOOK_USER);
        if (is_null($op)) {
            return [];
        }

        try {
            $positions = $this->getPositionsWithOpSet((int) $op->getOperationId());
        } catch (\ilPositionPermissionsNotActive $e) {
            $positions = [];
        }

        return $positions;
    }

    /**
     * @return ilOrgUnitOperation | null
     */
    protected function getOperationIdFor(string $operation)
    {
        $op = array_shift(
            ilOrgUnitOperation::where(
                array(
                    'operation_string' => $operation
                )
            )->get()
        );

        return $op;
    }

    protected function getPositionsWithOpSet(int $op_id) : array
    {
        $ret = [];

        $positions = ilOrgUnitPosition::get();
        foreach ($positions as $position) {
            $ilOrgUnitPermission = ilOrgUnitPermissionQueries::getSetForRefId($this->getRefId(), $position->getId());
            if ($ilOrgUnitPermission->isOperationIdSelected($op_id)) {
                $ret[] = $position;
            }
        }

        return $ret;
    }

    public function getSearchLocationRefId() : int
    {
        if (!$this->getSettings()->isLocal()) {
            return self::REPOSITORY_REF_ID;
        }

        global $DIC;
        $tree = $DIC["tree"];
        $data = $tree->getNodeTreeData($this->getRefId());
        return (int) $data["parent"];
    }
}
