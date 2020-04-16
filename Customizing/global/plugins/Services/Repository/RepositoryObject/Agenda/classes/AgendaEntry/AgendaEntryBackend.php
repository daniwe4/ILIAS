<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\AgendaEntry;

use CaT\Plugins\Agenda\TableProcessing\backend;
use CaT\Plugins\Agenda\TableProcessing\TableProcessor;

require_once __DIR__ . "/class.ilAgendaEntriesGUI.php";

class AgendaEntryBackend implements backend
{
    /**
     * @var ilDB
     */
    protected $db;

    public function __construct(ilDB $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function delete(array $record)
    {
        $object = $record[TableProcessor::KEY_OBJECT];
        $this->db->delete($object->getId());
    }

    /**
     * @inheritDoc
     */
    public function valid(array $record) : array
    {
        $object = $record[TableProcessor::KEY_OBJECT];
        if (is_null($object->getPoolItemId())) {
            $record[TableProcessor::KEY_ERROR][\ilAgendaEntriesGUI::F_POOL_ITEM][] = "no_pool_item_selected";
        }
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function update(array $record) : array
    {
        $object = $record[TableProcessor::KEY_OBJECT];
        $this->db->update($object);
        $record[TableProcessor::KEY_MESSAGE][] = "update_succesfull";
        return $record;
    }

    /**
     * @inheritDoc
     */
    public function create(array $record) : array
    {
        $object = $record[TableProcessor::KEY_OBJECT];
        $n_object = $this->db->create(
            $object->getObjId(),
            $object->getPoolItemId(),
            $object->getDuration(),
            $object->getPosition(),
            $object->getIsBlank(),
            $object->getAgendaItemContent(),
            $object->getGoals()
        );

        $record[TableProcessor::KEY_OBJECT] = $n_object->withIDDTime($object->getIDDTime());
        $record[TableProcessor::KEY_MESSAGE][] = "created_succesfull";
        return $record;
    }
}
