<?php

namespace CaT\Plugins\BookingModalities\TableProcessing;

class TableProcessor
{
    const ACTION_SAVE = "save";
    const ACTION_DELETE = "delete";

    public function __construct(backend $backend)
    {
        $this->backend = $backend;
    }

    public function process(array $records, array $actions)
    {
        $delete = in_array(self::ACTION_DELETE, $actions);
        $save = in_array(self::ACTION_SAVE, $actions);

        foreach ($records as $key => $record) {
            if ($delete && $record["delete"]) {
                if ($record["object"]->getId() != -1) {
                    $this->deleteRecord($record);
                }

                unset($records[$key]);
            }

            if ($save && !$record["delete"]) {
                $records[$key] = $this->saveRecord($record);
            }
        }

        return $records;
    }

    protected function saveRecord(array $record)
    {
        $record = $this->backend->valid($record);

        if (count($record["errors"]) > 0) {
            return $record;
        }

        if ($record["object"]->getId() == -1) {
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
