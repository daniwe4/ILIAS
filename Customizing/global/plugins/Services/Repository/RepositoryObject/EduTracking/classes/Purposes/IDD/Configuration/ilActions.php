<?php

namespace CaT\Plugins\EduTracking\Purposes\IDD\Configuration;

class ilActions
{
    /**
     * @var \ilEduTrackingPlugin
     */
    protected $plugin;

    /**
     * @var DB
     */
    protected $db;

    public function __construct(\ilEduTrackingPlugin $plugin, DB $db)
    {
        $this->plugin = $plugin;
        $this->db = $db;
    }

    /**
     * Get the current configuration entries
     *
     * @return ConfigWBD
     */
    public function select()
    {
        return $this->db->select();
    }

    /**
     * Create a new configuration entry
     *
     * @param bool 	$available
     * @param int 	$changed_by
     *
     * @return void
     */
    public function create($available, $changed_by)
    {
        $this->db->insert($available, $changed_by);
    }

    /**
     * Get the plugin object
     *
     * @throws \Exception if no plugin is set
     *
     * @return \ilEduTrackingPlugin
     */
    public function getPlugin()
    {
        if ($this->plugin === null) {
            throw new \Exception("no plugin object set");
        }

        return $this->plugin;
    }
}
