<?php
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting;

use CaT\Plugins\Accounting;
use CaT\Plugins\Accounting\Data\Export;

/**
 * Communication class between front- and backend.
 * E.g. GUI only use this class to get information from ILIAS DB.
 */
class ilObjectActions
{
    const F_DATA_CHK = "data_chk";

    /**
     * @var Accounting\Data\DB
     */
    protected $data_db;

    /**
     * @var Accounting\Settings\DB
     */
    protected $settings_db;

    /**
     * @var Accounting\Config\CostType\DB
     */
    protected $costtype_db;

    /**
     * @var Accounting\Config\VatRate\DB
     */
    protected $vat_rate_db;

    /**
     * @var Accounting\ilObjAccounting
     */
    protected $object;

    /**
     * @var \ilAppEventHandler
     */
    protected $app_event_handler;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    public function __construct(
        $object,
        Accounting\Data\ilDB $data_db,
        Accounting\Settings\ilDB $settings_db,
        \ilAppEventHandler $app_event_handler,
        Fees\Fee\ilActions $fee_actions,
        Fees\CancellationFee\ilActions $cancellation_fee_actions
    ) {
        global $DIC;

        $this->setObject($object);
        $this->setDataDB($data_db);
        $this->setSettingsDB($settings_db);
        $this->setAppEventHandler($app_event_handler);
        $this->g_access = $DIC->access();
        $this->fee_actions = $fee_actions;
        $this->cancellation_fee_actions = $cancellation_fee_actions;
    }

    protected function setDataDB(Accounting\Data\DB $value)
    {
        $this->data_db = $value;
    }

    public function insertData(Accounting\Data\Data $entry)
    {
        $this->data_db->insert($entry);
    }

    public function updateData(Accounting\Data\Data $entry)
    {
        $this->data_db->update($entry);
    }

    /**
     * @return Accounting\Data\Data[]
     */
    public function readData() : array
    {
        return $this->data_db->read();
    }

    public function deleteData(int $id)
    {
        $this->data_db->deleteFor($id);
    }

    /**
     * @return Accounting\Data\Data[]
     */
    public function selectFor(int $id) : array
    {
        return $this->data_db->selectFor($id);
    }

    public function getNumDBEntries() : int
    {
        return $this->data_db->getNumDBEntries();
    }

    public function getNetSum() : float
    {
        return $this->data_db->getNetSum($this->getObjId());
    }

    public function getGrossSum() : float
    {
        return $this->data_db->getGrossSum($this->getObjId());
    }

    protected function setSettingsDB(Accounting\Settings\DB $value)
    {
        $this->settings_db = $value;
    }

    public function createSettings(bool $finalized, bool $edit_fee)
    {
        $this->settings_db->insert($this->getObjId(), $finalized, $edit_fee);
    }

    public function updateSetting(Accounting\Settings\Settings $entry)
    {
        $this->settings_db->update($entry);
    }

    public function selectSettings() : Accounting\Settings\Settings
    {
        return $this->settings_db->selectFor($this->getObjId());
    }

    public function deleteSettings()
    {
        $this->settings_db->deleteFor($this->getObjId());
    }

    public function getAppEventHandler() : \ilAppEventHandler
    {
        return $this->app_event_handler;
    }

    public function setAppEventHandler(\ilAppEventHandler $value)
    {
        $this->app_event_handler = $value;
    }

    public function getObject()
    {
        if ($this->object === null) {
            throw new LogicException(__METHOD__ . " no object found!");
        }
        return $this->object;
    }

    private function setObject($value)
    {
        ;
        $this->object = $value;
    }

    public function getObjId() : int
    {
        return (int) $this->getObject()->getId();
    }

    public function getParentCourse() : \ilObjCourse
    {
        return $this->getObject()->getParentCourse();
    }

    /**
     * Raise Accounting updated event
     */
    public function updatedEvent()
    {
        $e = array("xacc" => $this->getObject());
        $this->getAppEventHandler()->raise("Plugin/Accounting", "updateAccounting", $e);
    }

    /**
     * Raise accounting deleted event
     */
    public function deletedEvent()
    {
        $e = array(
            "xacc" => $this,
            "object" => $this->getParentCourse()
        );

        $this->getAppEventHandler()->raise("Plugin/Accounting", "deleteAccounting", $e);
    }

    /**
     * Update the finalize status to true
     */
    public function finalize()
    {
        $finalized = true;

        $this->getObject()->updateSettings(function ($s) use ($finalized) {
            return $s
                ->withFinalized($finalized)
                ;
        });
        $this->getObject()->update();
    }

    public function getFeeActions() : Accounting\Fees\Fee\ilActions
    {
        return $this->fee_actions;
    }

    public function getCancellationFeeActions() : Accounting\Fees\CancellationFee\ilActions
    {
        return $this->cancellation_fee_actions;
    }
}
