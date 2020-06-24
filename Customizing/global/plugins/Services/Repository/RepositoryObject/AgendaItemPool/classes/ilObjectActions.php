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
    public function getAllAgendaItemsByPoolId(int $pool_id, $offset = null, $limit = null)
    {
        return $this->agenda_item_db->getAllAgendaItemsByPoolId($pool_id, $offset, $limit);
    }

    /**
     * Get count of all agenda items from database.
     *
     * @param 	int 	$pool_id
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItemsCountByPoolId(int $pool_id)
    {
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
        string $title,
        \DateTime $last_change,
        int $change_usr_id,
        ?int $pool_id,
        ?string $description = null,
        bool $is_active = true,
        bool $idd_relevant = false,
        bool $is_deleted = false,
        bool $is_blank = false,
        array $training_topics = null,
        ?string $goals = null,
        ?string $gdv_learning_content = null,
        ?string $idd_learning_content = null,
        ?string $agenda_item_content = null
    ) {
        assert($this->checkIntArray($training_topics));

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
    public function getAgendaItemById(int $obj_id)
    {
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
    public function deleteAgendaItemsByPoolId(int $pool_id)
    {
        $this->agenda_item_db->deleteByPoolId($pool_id);
    }

    /**
     * Check whether the id is valid.
     *
     * @param 	int 	$obj_id
     * @return 	bool
     */
    public function isValidObjId(int $obj_id)
    {
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
    public function createSettings(int $obj_id)
    {
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
    public function getSettingsById(int $obj_id)
    {
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
    public function deleteSettingById(int $id)
    {
        $this->settings_db->deleteFor($id);
    }

    /**
     * Checks whether the AgendaItemPool is active.
     *
     * @param 	int 	$id
     * @return 	void
     */
    public function isAgendaItemPoolActive(int $id)
    {
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
