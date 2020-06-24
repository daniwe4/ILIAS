<?php

namespace CaT\Plugins\Agenda\AgendaEntry;

class ilActions
{
    public function __construct(\ilObjAgenda $object, DB $db)
    {
        $this->object = $object;
        $this->agenda_entry_db = $db;
    }

    /**
     * Create a agenda item object also as db entry
     *
     * @param int 	$obj_id
     * @param int 	$pool_item_id
     * @param \DateTime 	$start_time
     * @param \DateTime 	$end_time
     * @param bool 	$is_blank
     * @param string | null 	$agenda_item_content
     * @param string | null 	$goals
     *
     * @return AgendaEntry
     */
    public function create(
        int $obj_id,
        int $pool_item_id,
        \DateTime $start_time,
        \DateTime $end_time,
        bool $is_blank = false,
        ?string $agenda_item_content = null,
        ?string $goals = null
    ) {
        return $this->agenda_entry_db->create(
            $obj_id,
            $pool_item_id,
            $start_time,
            $end_time,
            $is_blank,
            $agenda_item_content,
            $goals
        );
    }

    /**
     * Updates an existing agenda item
     *
     * @param AgendaEntry	$item
     *
     * @return void
     */
    public function update(AgendaEntry $item)
    {
        $this->agenda_entry_db->update($item);
    }

    /**
     * Deletes an existing agenda item by id
     *
     * @param int 	$id
     *
     * @return void
     */
    public function delete(int $id)
    {
        $this->agenda_entry_db->delete($id);
    }

    /**
     * Delete all agenda entries for agegenda
     *
     * @return void
     */
    public function deleteAll()
    {
        $this->agenda_entry_db->deleteFor((int) $this->getObject()->getId());
    }

    /**
     * Select all agenda entries for agenda
     *
     * @return AgendaEntry[]
     */
    public function selectAll()
    {
        return $this->agenda_entry_db->selectFor((int) $this->getObject()->getId());
    }

    /**
     * Get a new agenda entry instance
     *
     * @return AgendaEntry
     */
    public function getNewEntry()
    {
        return $this->agenda_entry_db->getNewEntry((int) $this->getObject()->getId());
    }

    /**
     * Get min start and max end of all agenda entries
     *
     * @return string[]
     */
    public function getDayStartAndEnd()
    {
        return $this->agenda_entry_db->getDayStartAndEnd((int) $this->getObject()->getId());
    }

    /**
     * Get the current object
     *
     * @throws Exception 	If no obejct is set.
     *
     * @return \ilObjAgenda
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \Exception("No object is set");
        }

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

    public function selectForId(int $id) : AgendaEntry
    {
        return $this->agenda_entry_db->selectForId($id);
    }
}
