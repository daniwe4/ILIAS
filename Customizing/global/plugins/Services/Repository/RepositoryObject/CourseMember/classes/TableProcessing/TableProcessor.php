<?php

namespace CaT\Plugins\CourseMember\TableProcessing;

/**
 * Proceeds aktions on table values
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class TableProcessor
{
    const ACTION_SAVE = "save";
    const ACTION_DELETE = "delete";

    public function __construct(backend $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Proceeds requested actions
     *
     * @param array 	$records
     * @param string[]	$actions
     *
     * @return array
     */
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

    /**
     * Executes action save
     *
     * @param array 	$record
     *
     * @return array
     */
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

    /**
     * Executes action delete
     *
     * @param array 	$record
     *
     * @return void
     */
    protected function deleteRecord(array $record)
    {
        $this->backend->delete($record);
    }
}
