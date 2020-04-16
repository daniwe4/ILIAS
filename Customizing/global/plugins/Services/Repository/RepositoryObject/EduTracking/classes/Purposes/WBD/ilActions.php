<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

/**
 * Actions for WBD settings in repository
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilActions
{
    /**
     * @var \ilObjEduTracking
     */
    protected $object;

    /**
     * @var DB
     */
    protected $db;

    public function __construct(\ilObjEduTracking $object, DB $db)
    {
        $this->object = $object;
        $this->db = $db;
    }

    /**
     * Creates an empty settings object
     *
     * @return WBD
     */
    public function createEmpty()
    {
        return $this->db->create($this->getObject());
    }

    /**
     * Selects the current settings
     *
     * @return WBD
     */
    public function select()
    {
        return $this->db->selectFor($this->getObject());
    }

    /**
     * Updates current settings
     *
     * @param WBD 	$settings
     *
     * @return void
     */
    public function update(WBD $settings)
    {
        $settings->update();
    }

    /**
     * Delete current settings
     *
     * @return void
     */
    public function delete()
    {
        $this->db->deleteFor($this->getObject());
    }

    /**
     * Get the current object
     *
     * @throws \Exception if no object is set
     *
     * @return \ilObjEduTracking
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object is set");
        }

        return $this->object;
    }
}
