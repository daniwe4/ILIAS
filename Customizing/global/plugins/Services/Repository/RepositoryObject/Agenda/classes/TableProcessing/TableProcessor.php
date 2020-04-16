<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\TableProcessing;

class TableProcessor
{
    const ACTION_SAVE = "save";
    const ACTION_DELETE = "delete";

    const KEY_OBJECT = "object";
    const KEY_ERROR = "error";
    const KEY_DELETE = "delete";
    const KEY_MESSAGE = "message";

    public function __construct(backend $backend)
    {
        $this->backend = $backend;
    }

    public function process(array $records, array $actions)
    {
        $delete = in_array(self::ACTION_DELETE, $actions);
        $save = in_array(self::ACTION_SAVE, $actions);

        foreach ($records as $key => $record) {
            if ($record[self::KEY_OBJECT]->getPoolItemId() == -2) {
                continue;
            }

            if ($delete && $record[self::KEY_DELETE]) {
                if ($record[self::KEY_OBJECT]->getId() != -1) {
                    $this->deleteRecord($record);
                }

                unset($records[$key]);
            }

            if ($save && !$record[self::KEY_DELETE]) {
                $records[$key] = $this->saveRecord($record);
            }
        }

        return $records;
    }

    protected function saveRecord(array $record)
    {
        $record = $this->backend->valid($record);

        if (count($record[self::KEY_ERROR]) > 0) {
            return $record;
        }

        if ($record[self::KEY_OBJECT]->getId() < 0) {
            return $this->backend->create($record);
        } else {
            return $this->backend->update($record);
        }
    }

    protected function deleteRecord(array $record)
    {
        $this->backend->delete($record);
    }
}
