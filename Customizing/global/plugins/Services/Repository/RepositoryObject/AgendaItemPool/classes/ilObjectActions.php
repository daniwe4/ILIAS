<?php
namespace CaT\Plugins\AgendaItemPool;

use CaT\Plugins\AgendaItemPool\Helper;

/**
 * Class ilObjectActions.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjectActions
{
    use Helper;

    /**
     * @var ilObjAgendaItemPool
     */
    protected $object;

    /**
     * @var AgendaItem\ilDB
     */
    protected $agenda_item_db;

    /**
     * @var Settings\ilDB
     */
    protected $settings_db;

    /**
     * Constructor of the class ilObjectActions
     */
    public function __construct(
        \ilObjAgendaItemPool $object,
        AgendaItem\ilDB $agenda_item_db,
        Settings\ilDB $settings_db
    ) {
        $this->object = $object;
        $this->agenda_item_db = $agenda_item_db;
        $this->settings_db = $settings_db;
    }


    /**********************************************************************
     *							AgendaItem
     **********************************************************************/
    /**
     * Get all agenda items from database.
     *
     * @param 	int 	$pool_id
     * @param 	int | null 	$offset
     * @param 	int | null 	$limit
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItemsByPoolId($pool_id, $offset = null, $limit = null)
    {
        assert('is_int($pool_id)');
        return $this->agenda_item_db->getAllAgendaItemsByPoolId($pool_id, $offset, $limit);
    }

    /**
     * Get count of all agenda items from database.
     *
     * @param 	int 	$pool_id
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItemsCountByPoolId($pool_id)
    {
        assert('is_int($pool_id)');
        return $this->agenda_item_db->getAllAgendaItemsCountByPoolId($pool_id);
    }

    /**
     * Crate a new agenda item and give it back.
     *
     * @param 	string 			$title
     * @param 	DateTime 		$last_change
     * @param 	int 			$change_usr_id
     * @param 	int|null		$pool_id
     * @param 	string|null		$description
     * @param 	bool 			$is_active
     * @param 	bool 			$idd_relevant
     * @param 	bool 			$is_deleted
     * @param 	bool 			$is_blank
     * @param 	int[]|null		$training_topics
     * @param 	string|null		$goals
     * @param 	string			$gdv_learning_content
     * @param 	string			$idd_learning_content
     * @param 	string|null		$agenda_item_content
     * @return 	AgendaItem
     */
    public function createAgendaItem(
        $title,
        \DateTime $last_change,
        $change_usr_id,
        $pool_id,
        $description = null,
        $is_active = true,
        $idd_relevant = false,
        $is_deleted = false,
        $is_blank = false,
        array $training_topics = null,
        $goals = null,
        $gdv_learning_content = null,
        $idd_learning_content = null,
        $agenda_item_content = null
    ) {
        assert('is_string($title)');
        assert('is_string($description) || is_null($description)');
        assert('is_bool($is_active)');
        assert('is_bool($idd_relevant)');
        assert('is_bool($is_deleted)');
        assert('is_int($change_usr_id)');
        assert('is_int($pool_id) || is_null($pool_id)');
        assert('is_bool($is_blank)');
        assert('$this->checkIntArray($training_topics)');
        assert('is_string($goals) || is_null($goals)');
        assert('is_string($gdv_learning_content) || is_null($gdv_learning_content)');
        assert('is_string($idd_learning_content) || is_null($gdv_learning_content)');
        assert('is_string($agenda_item_content) || is_null($agenda_item_content)');

        return $this->agenda_item_db->create(
            $title,
            $last_change,
            $change_usr_id,
            $pool_id,
            $description,
            $is_active,
            $idd_relevant,
            $is_deleted,
            $is_blank,
            $training_topics,
            $goals,
            $gdv_learning_content,
            $idd_learning_content,
            $agenda_item_content
        );
    }

    /**
     * Get an agenda item by obj id.
     *
     * @param 	int 	$obj_id
     * @return 	AgendaItem
     */
    public function getAgendaItemById($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->agenda_item_db->selectFor($obj_id);
    }

    /**
     * Update an db entry for an agenda item.
     *
     * @param 	AgendaItem\AgendaItem 	$ai
     * @return 	void
     */
    public function updateAgendaItem(AgendaItem\AgendaItem $ai)
    {
        $this->agenda_item_db->update($ai);
    }

    /**
     * Delete agenda items.
     *
     * @param 	AgendaItem[]
     * @return 	void
     */
    public function deleteAgendaItems(array $ids)
    {
        $this->agenda_item_db->deleteFor($ids);
    }

    /**
     * Delete agenda items by pool id.
     *
     * @param 	int 	$pool_id
     * @return 	void
     */
    public function deleteAgendaItemsByPoolId($pool_id)
    {
        assert('is_int($pool_id)');
        $this->agenda_item_db->deleteByPoolId($pool_id);
    }

    /**
     * Check whether the id is valid.
     *
     * @param 	int 	$obj_id
     * @return 	bool
     */
    public function isValidObjId($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->agenda_item_db->isValidObjId($obj_id);
    }


    /**********************************************************************
     *							Settings
     **********************************************************************/
    /**
     * Create a new setting entry.
     *
     * @param 	int 	$obj_id
     * @return 	void
     */
    public function createSettings($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->settings_db->create($obj_id);
    }

    /**
     * Get all obj_id's.
     *
     * @return 	int[]
     */
    public function getObjIds()
    {
        return $this->settings_db->getObjIds();
    }

    /**
     * Get settings by obj_id.
     *
     * @param 	int 	$obj_id
     * @return 	Settings
     */
    public function getSettingsById($obj_id)
    {
        assert('is_int($obj_id)');
        return $this->settings_db->selectFor($obj_id);
    }

    /**
     * Update settings.
     *
     * @param 	Settings 	$settings
     * @return 	void
     */
    public function updateSettings(Settings\Settings $settings)
    {
        $this->settings_db->update($settings);
    }

    /**
     * Delete setting object by id.
     *
     * @param 	int 	$id
     * @return 	void
     */
    public function deleteSettingById($id)
    {
        assert('is_int($id)');
        $this->settings_db->deleteFor($id);
    }

    /**
     * Checks whether the AgendaItemPool is active.
     *
     * @param 	int 	$id
     * @return 	void
     */
    public function isAgendaItemPoolActive($id)
    {
        assert('is_int($id)');
        $this->settings_db->isActive($id);
    }

    /**********************************************************************
     *							Miscellaneous
     **********************************************************************/
    /**
     * Get the root plugin directory.
     *
     * @return 	string
     */
    public function getPluginDirectory()
    {
        return $this->object->getPluginDirectory();
    }

    /**
     * Get an instance of the object.
     *
     * @return \ilObjAgendaItemPool
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Checks the edu tracking plugin is active
     *
     * @return bool
     */
    public function isEduTrackingActive()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        return \ilPluginAdmin::isPluginActive("xetr");
    }
}
