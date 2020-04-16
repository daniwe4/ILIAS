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
    public function getTableData()
    {
        return $this->db->select();
    }

    /**
     * @inheritdoc
     */
    public function create($agenda_item_id, $caption)
    {
        assert('is_string($caption)');
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
    public function delete($topic_id, $caption_id)
    {
        assert('is_int($topic_id)');
        $this->db->delete($topic_id);
    }
}
