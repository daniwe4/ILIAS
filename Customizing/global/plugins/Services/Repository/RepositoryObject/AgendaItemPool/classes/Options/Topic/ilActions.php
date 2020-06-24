<?php

namespace CaT\Plugins\AgendaItemPool\Options\Topic;

use CaT\Plugins\AgendaItemPool\Options\ilActions as ilOptionActions;
use CaT\Plugins\AgendaItemPool\Options\Option;

/**
 * Actions implementation for topic options
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilActions extends ilOptionActions
{
    public function __construct(ilDB $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getTableData() : array
    {
        return $this->db->select();
    }

    /**
     * @inheritdoc
     */
    public function create(string $agenda_item_id, string $caption) : Option
    {
        return $this->db->create($caption);
    }

    /**
     * @inheritdoc
     */
    public function update(Option $option)
    {
        $this->db->update($option);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $topic_id, int $caption_id) : void
    {
        $this->db->delete($topic_id);
    }
}
