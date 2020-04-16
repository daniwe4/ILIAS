<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * Configuration for CalBuilder
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalConfig
{
    /**
     * @var int
     */
    protected $base_ref_id;
    /**
     * @var int
     */
    protected $tep_obj_id;
    /**
     * @var array
     */
    protected $users;
    /**
     * @var \DateTime
     */
    protected $start;
    /**
     * @var \DateTime
     */
    protected $end;
    /**
     * @var string[]
     */
    protected $selected_cols;

    public function __construct(
        int $tep_obj_id,
        int $base_ref_id,
        array $users,
        \DateTime $start,
        \DateInterval $interval,
        array $selected_cols
    ) {
        $this->tep_obj_id = $tep_obj_id;
        $this->base_ref_id = $base_ref_id;
        $this->users = $users;
        $this->start = $start;
        $this->interval = $interval;
        $this->selected_cols = $selected_cols;
    }

    public function getTEPObjId() : int
    {
        return $this->tep_obj_id;
    }

    public function getBaseRefId() : int
    {
        return $this->base_ref_id;
    }

    public function getUserIds() : array
    {
        return $this->users;
    }

    public function getStart() : \DateTime
    {
        return $this->start;
    }


    public function getEnd() : \DateTime
    {
        $end = clone $this->start;
        $end->add($this->interval);
        return $end;
    }

    public function getSelectedColumns() : array
    {
        return $this->selected_cols;
    }
}
