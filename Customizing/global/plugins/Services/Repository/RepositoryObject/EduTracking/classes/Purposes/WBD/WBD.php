<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

class WBD
{
    /**
     * @var DB
     */
    protected $db;

    /**
     * @var \ilObjEduTracking
     */
    protected $obj;

    /**
     * @var string
     */
    protected $education_type;

    /**
     * @var string
     */
    protected $education_content;

    /**
     * @param	DB	$db
     * @param	\ilObjEduTracking	$obj
     * @param	string | null 	$education_type
     * @param	string | null 	$education_content
     */
    public function __construct(
        DB $db,
        \ilAppEventHandler $evt_handler,
        \ilObjEduTracking $obj,
        ?string $education_type = null,
        ?string $education_content = null
    ) {
        $this->obj = $obj;
        $this->db = $db;
        $this->evt_handler = $evt_handler;
        $this->education_type = $education_type;
        $this->education_content = $education_content;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj->getId();
    }

    /**
     * @return \ilObjEduTracking
     */
    public function getObject()
    {
        return $this->obj;
    }


    /**
     * @return string
     */
    public function getEducationType()
    {
        return $this->education_type;
    }

    /**
     * @return string
     */
    public function getEducationContent()
    {
        return $this->education_content;
    }

    /**
     * Get clone with education type
     *
     * @param string 	$education_type
     *
     * @return this
     */
    public function withEducationType(?string $education_type)
    {
        $clone = clone $this;
        $clone->education_type = $education_type;
        return $clone;
    }

    /**
     * Get clone with education content
     *
     * @param string 	$education_type
     *
     * @return this
     */
    public function withEducationContent(?string $education_content)
    {
        $clone = clone $this;
        $clone->education_content = $education_content;
        return $clone;
    }

    /**
     * Persist any changes performed on this object.
     *
     * @return	void
     */
    public function update()
    {
        $this->db->update($this);
        $obj = $this->getObject();
        $data = new WBDDataInterface(
            $this,
            $obj->getConfigWBD(),
            $obj->getWBDUserDataProvider(),
            $obj->getWBDObjectProvider()
        );
        $this->throwEvent(
            "updateWBD",
            (int) $obj->getId(),
            $data->getInternalId(),
            $data->getEducationType(),
            $data->getEducationContent()
        );
    }

    /**
     * Delete any persisted data associated with this object.
     *
     * @return	void
     */
    public function delete()
    {
        $this->db->deleteFor($this->obj);
    }

    /**
     * Throws event
     *
     * @param	string	$reason
     * @param	int	$crs_id
     * @param	int	$minutes
     *
     * @return void
     */
    protected function throwEvent(
        string $reason,
        int $obj_id,
        $internal_id,
        $wbd_learning_type,
        $wbd_learning_content
    ) {
        $payload = [
            "xetr_obj_id" => $obj_id,
            'internal_id' => $internal_id,
            'wbd_learning_type' => $wbd_learning_type,
            'wbd_learning_content' => $wbd_learning_content
        ];

        $this->evt_handler->raise("Plugin/EduTracking", $reason, $payload);
    }
}
