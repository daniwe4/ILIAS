<?php
namespace CaT\Plugins\Accomodation\Reservation;

use \CaT\Plugins\Accomodation;

/**
 * Backend implementation for table handling of user reservations
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class UserReservationsBackend implements Accomodation\TableProcessing\Backend
{
    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(Accomodation\ilActions $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @inheritdoc
     */
    public function delete($record)
    {
        $obj = $record['object'];
        $this->actions->deleteAllUserReservations($obj->getUserId());
    }

    /**
     * @inheritdoc
     */
    public function valid($record)
    {
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function update($record)
    {
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function create($record)
    {
        return $record;
    }
}
