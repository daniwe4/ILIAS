<?php

declare(strict_types=1);

namespace CaT\Plugins\EduTracking\Purposes\IDD;

class IDD
{
    /**
     * @var DB
     */
    protected $db;

    /**
     * @var	\ilObjEduTracking
     */
    protected $obj;

    /**
     * @var \ilAppEventHandler
     */
    protected $evt_handler;

    /**
     * @var int
     */
    protected $minutes;

    public function __construct(
        DB $db,
        \ilAppEventHandler $evt_handler,
        \ilObjEduTracking $obj,
        int $minutes = 0
    ) {
        $this->db = $db;
        $this->evt_handler = $evt_handler;
        $this->obj = $obj;
        $this->minutes = $minutes;
    }

    public function getObjId() : int
    {
        return (int) $this->obj->getId();
    }

    public function getObject() : \ilObjEduTracking
    {
        return $this->obj;
    }

    public function getMinutes() : int
    {
        return $this->minutes;
    }

    public function withMinutes(int $minutes = null) : IDD
    {
        $clone = clone $this;
        $clone->minutes = $minutes;
        return $clone;
    }

    public function update()
    {
        $this->db->update($this);
        $this->throwEvent("updateIDD", (int) $this->getObject()->getId(), (int) $this->minutes);
    }

    public function delete()
    {
        $this->db->deleteFor($this->obj);
    }

    protected function throwEvent(string $reason, int $obj_id, int $minutes)
    {
        $payload = [
            "xetr_obj_id" => $obj_id,
            "minutes" => $minutes
        ];

        $this->evt_handler->raise("Plugin/EduTracking", $reason, $payload);
    }
}
