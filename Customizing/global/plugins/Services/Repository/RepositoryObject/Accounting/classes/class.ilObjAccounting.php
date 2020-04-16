<?php
/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\Accounting;
use CaT\Ente\ILIAS\ilProviderObjectHelper;

require_once "Services/Repository/classes/class.ilObjectPlugin.php";
require_once __DIR__ . "/UnboundProvider.php";

class ilObjAccounting extends ilObjectPlugin implements Accounting\ObjAccounting
{
    use ilProviderObjectHelper;

    /**
     * @var CaT\Plugins\Accounting\Settings\Accounting
     */
    protected $xacc_settings;

    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    public function getObjectActions() : Accounting\ilObjectActions
    {
        global $DIC;

        if ($this->actions === null) {
            $data_db = $this->getAccountingDataDB();
            $settings_db = $this->getAccountingSettingsDB();
            $app_event_handler = $DIC["ilAppEventHandler"];
            $fee_actions = new Accounting\Fees\Fee\ilActions(
                $this,
                new Accounting\Fees\Fee\ilDB($DIC->database()),
                $app_event_handler
            );

            $cancellation_fee_actions = new Accounting\Fees\CancellationFee\ilActions(
                $this,
                new Accounting\Fees\CancellationFee\ilDB($DIC->database()),
                $app_event_handler
            );

            $this->actions = new Accounting\ilObjectActions(
                $this,
                $data_db,
                $settings_db,
                $app_event_handler,
                $fee_actions,
                $cancellation_fee_actions
            );
        }

        return $this->actions;
    }

    /**
     * @inheritdoc
     */
    public function getAccountingDataDB() : Accounting\Data\ilDB
    {
        global $ilDB, $ilUser;
        if ($this->data_db === null) {
            $this->data_db = new Accounting\Data\ilDB($ilDB, $ilUser);
        }

        return $this->data_db;
    }

    /**
     * @inheritdoc
     */
    public function getAccountingSettingsDB() : Accounting\Settings\ilDB
    {
        global $ilDB, $ilUser;
        if ($this->settings_db === null) {
            $this->settings_db = new Accounting\Settings\ilDB($ilDB, $ilUser);
        }

        return $this->settings_db;
    }

    /**
     * @inheritdoc
     */
    public function initType()
    {
        $this->setType("xacc");
    }

    /**
     * @inheritdoc
     */
    public function doCreate()
    {
        $this->getObjectActions()->createSettings(false, false);
        $this->createUnboundProvider(
            "crs",
            CaT\Plugins\Accounting\UnboundProvider::class,
            __DIR__ . "/UnboundProvider.php"
        );
    }

    /**
     * @inheritdoc
     */
    public function doUpdate()
    {
        $this->getObjectActions()->updateSetting($this->getSettings());
    }

    /**
     * @inheritdoc
     */
    public function doRead()
    {
        $this->setSettings($this->getObjectActions()->selectSettings());
    }

    /**
     * @inheritdoc
     */
    public function doDelete()
    {
        $this->getObjectActions()->deleteSettings();
        $this->deleteUnboundProviders();
    }

    /**
     * Get called if the object get be coppied.
     * Copy additional settings to new object
     */
    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
        $settings = $this->getSettings();
        $fnc = function ($s) use ($settings) {
            return $s->withEditFee($settings->getEditFee());
        };

        $new_obj->doRead();
        $new_obj->updateSettings($fnc);

        $fee = $this->getObjectActions()->getFeeActions()->select();
        $new_obj->getObjectActions()->getFeeActions()->create($fee->getFee());

        $cancellation_fee = $this->getObjectActions()->getCancellationFeeActions()->select();
        $new_obj->getObjectActions()->getCancellationFeeActions()->create(
            $cancellation_fee->getCancellationFee()
        );

        $new_obj->update();
        $new_obj->getObjectActions()->updatedEvent();
    }

    /**
     * @inheritdoc
     */
    public function updateSettings(\Closure $update)
    {
        $this->xacc_settings = $update($this->getSettings());
    }

    /**
     * @inheritdoc
     */
    public function setSettings(Accounting\Settings\Settings $xacc)
    {
        $this->xacc_settings = $xacc;
    }

    /**
     * @inheritdoc
     */
    public function getSettings()
    {
        return $this->xacc_settings;
    }

    /**
     * @inheritdoc
     */
    public function getParentCourse()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        $parents = $tree->getPathFull($this->getRefId());
        $parent = array_filter($parents, function ($p) {
            if ($p["type"] == "crs") {
                return $p;
            }
        });

        if (count($parent) > 0) {
            $parent_crs = array_shift($parent);
            require_once("Services/Object/classes/class.ilObjectFactory.php");
            return ilObjectFactory::getInstanceByRefId($parent_crs["ref_id"]);
        }

        return null;
    }

    public function txtClosure() : \Closure
    {
        return function (string $code) {
            return $this->txt($code);
        };
    }

    /**
     * @inheritdoc
     */
    public function afterCourseCreation($config)
    {
        foreach ($config as $key => $value) {
            if ($key == "fee") {
                $fee = $this->getObjectActions()->getFeeActions()->select();
                $fee = $fee->withFee((float) $value);
                $this->getObjectActions()->getFeeActions()->update($fee);
            } else {
                throw new \RuntimeException("Can't process configuration '$key'");
            }
        }
    }

    public function directory() : string
    {
        return $this->getPlugin()->getDirectory();
    }

    public function prefix() : string
    {
        return $this->getPlugin()->getPrefix();
    }

    public function getPluginObject()
    {
        return $this->getPlugin();
    }
}
