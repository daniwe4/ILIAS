<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Fees\Fee;

class ilActions
{
    /**
     * @var \ilObjAccounting
     */
    protected $object;

    /**
     * @var DB
     */
    protected $fee_db;

    public function __construct(
        \ilObjAccounting $object,
        DB $fee_db,
        \ilAppEventHandler $app_event_handler
    ) {
        $this->object = $object;
        $this->fee_db = $fee_db;
        $this->app_event_handler = $app_event_handler;
    }

    public function create(float $fee_value = null) : Fee
    {
        return $this->fee_db->create((int) $this->getObject()->getId(), $fee_value);
    }

    public function update(Fee $fee)
    {
        $this->fee_db->update($fee);
        $this->updatedEvent();
    }

    protected function updatedEvent()
    {
        $e = array("xacc" => $this->getObject());
        $this->getAppEventHandler()->raise("Plugin/Accounting", "updateFee", $e);
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

    public function select() : Fee
    {
        return $this->fee_db->select((int) $this->getObject()->getId());
    }

    public function delete()
    {
        $this->fee_db->delete((int) $this->getObject()->getId());
    }
}
