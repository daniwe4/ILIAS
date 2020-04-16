<?php

namespace CaT\Plugins\EduTracking\Purposes\IDD;

/**
 * Actions for IDD settings in repository
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

    /**
     * @var \ilAppEventHandler
     */
    protected $app_event_handler;

    public function __construct(\ilObjEduTracking $object, DB $db)
    {
        $this->object = $object;
        $this->db = $db;
        $this->app_event_handler = $app_event_handler;
    }

    /**
     * Creates an empty settings object
     *
     * @return IDD
     */
    public function createEmpty()
    {
        return $this->db->create($this->getObject());
    }

    /**
     * Selects the current settings
     *
     * @return IDD
     */
    public function select()
    {
        return $this->db->selectFor($this->getObject());
    }

    /**
     * Updates current settings
     *
     * @param IDD 	$settings
     *
     * @return void
     */
    public function update(IDD $settings)
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
