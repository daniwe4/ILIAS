<?php

namespace CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

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
     * @return Categories[]
     */
    public function selectCategories() : array
    {
        return $this->db->selectCategories();
    }

    public function getTitleById(int $category_id) : string
    {
        return $this->db->getTitleById($category_id);
    }

    /**
     * @param string[]
     */
    public function insertCategories(array $categories, int $changed_by)
    {
        $this->db->insertCategories($categories, $changed_by);
    }

    /**
     * @param int[]
     */
    public function deleteCategories(array $ids)
    {
        $this->db->deleteCategories($ids);
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
