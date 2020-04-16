<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\CancellationFee;

class ilActions
{
    /**
     * @var \ilObjAccounting
     */
    protected $object;

    /**
     * @var DB
     */
    protected $cancellation_fee_db;

    public function __construct(
        \ilObjAccounting $object,
        DB $cancellation_fee_db,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->object = $object;
        $this->cancellation_fee_db = $cancellation_fee_db;
        $this->app_event_handler = $app_event_handler;
    }

    public function create(float $cancellation_fee_value = null) : CancellationFee
    {
        return $this->cancellation_fee_db->create((int) $this->getObject()->getId(), $cancellation_fee_value);
    }

    public function update(CancellationFee $cancellation_fee)
    {
        $this->cancellation_fee_db->update($cancellation_fee);
        $this->updatedEvent();
    }

    protected function updatedEvent()
    {
        $e = array("xacc" => $this->getObject());
        $this->getAppEventHandler()->raise("Plugin/Accounting", "updateCancellationFee", $e);
    }

    public function getObject() : \ilObjAccounting
    {
        if ($this->object === null) {
            throw new LogicException(__METHOD__ . " no object found!");
        }
        return $this->object;
    }

    public function getAppEventHandler() : \ilAppEventHandler
    {
        return $this->app_event_handler;
    }

    public function select() : CancellationFee
    {
        return $this->cancellation_fee_db->select((int) $this->getObject()->getId());
    }

    public function delete()
    {
        $this->cancellation_fee_db->delete((int) $this->getObject()->getId());
    }
}
