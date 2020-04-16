<?php
namespace CaT\Plugins\BookingModalities\Settings\SelectableReasons;

use CaT\Plugins\BookingModalities\TableProcessing\backend;
use CaT\Plugins\BookingModalities\ilActions;

/**
 * TableProcessor backend to handle reason db actions
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class SelectableReasonsBackend implements backend
{
    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(ilActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * Delete the reason in record
     *
     * @param array
     *
     * @return null
     */
    public function delete($record)
    {
        $reason = $record["object"];
        $this->actions->deleteSelectableReason($reason->getId());
    }

    /**
     * Checks reason in record if it is valid
     * If not fills key errors with values
     *
     * @param array
     *
     * @return array
     */
    public function valid($record)
    {
        $reason = $record['object'];

        if (is_null($reason->getReason()) || $reason->getReason() == "") {
            $record['errors']['reason'][] = "pls_enter_reason";
        }
        return $record;
    }

    /**
     * Update an existing reason
     *
     * @param array
     *
     * @return array
     */
    public function update($record)
    {
        $reason = $record["object"];
        $this->actions->updateSelectableReason($reason);
        $record["message"][] = "update_succesfull";

        return $record;
    }

    /**
     * Creates a new reason
     *
     * @param array
     *
     * @return array
     */
    public function create($record)
    {
        $reason = $record["object"];
        $record["object"] = $this->actions->createSelectableReason($reason->getReason(), $reason->getActive());
        $record["message"][] = "created_succesfull";

        return $record;
    }
}
