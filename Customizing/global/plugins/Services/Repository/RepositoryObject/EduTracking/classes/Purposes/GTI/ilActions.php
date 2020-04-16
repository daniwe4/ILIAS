<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI;

/**
 * Actions for GTI settings in repository
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
     * @var Configuration\ilActions
     */
    protected $config_actions;

    public function __construct(
        \ilObjEduTracking $object,
        DB $db,
        Configuration\ilActions $config_actions
    ) {
        $this->object = $object;
        $this->db = $db;
        $this->config_actions = $config_actions;
    }

    /**
     * Creates an empty settings object
     *
     * @return GTI
     */
    public function createEmpty()
    {
        return $this->db->create($this->getObject());
    }

    /**
     * Selects the current settings
     *
     * @return GTI
     */
    public function select()
    {
        return $this->db->selectFor($this->getObject());
    }

    /**
     * Updates current settings
     *
     * @param GTI 	$settings
     *
     * @return void
     */
    public function update(GTI $settings)
    {
        $this->db->update($settings);
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

    public function getConfigActions()
    {
        return $this->config_actions;
    }
}
