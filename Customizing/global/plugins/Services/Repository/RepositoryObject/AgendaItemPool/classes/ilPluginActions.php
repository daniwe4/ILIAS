<?php
namespace CaT\Plugins\AgendaItemPool;

/**
 * Class ilObjectActions.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilPluginActions
{
    /**
     * @var ilAgendaItemPoolPlugin
     */
    protected $object;

    /**
     * @var AgendaItem\ilDB
     */
    protected $agenda_item_db;

    /**
     * Constructor of the class ilObjectActions
     */
    public function __construct(
        \ilAgendaItemPoolPlugin $object,
        AgendaItem\ilDB $agenda_item_db
    ) {
        $this->object = $object;
        $this->agenda_item_db = $agenda_item_db;
    }

    /**
     * Get all agenda items from database.
     *
     * @return 	AgendaItem[]
     */
    public function getAllAgendaItems()
    {
        return $this->agenda_item_db->getAllAgendaItems();
    }

    /**
     * Get agenda item by item id
     *
     * @param int 	$obj_id
     *
     * @return AgendaItem
     */
    public function getAgendaItemById(int $obj_id)
    {
        return $this->agenda_item_db->selectFor($obj_id);
    }
}
