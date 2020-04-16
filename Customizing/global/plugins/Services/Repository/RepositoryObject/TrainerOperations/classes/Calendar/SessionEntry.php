<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

/**
 * This is an Entry for Sessions.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class SessionEntry extends Entry
{
    /**
     * @var int
     */
    protected $session_ref_id;

    /**
     * @var int
     */
    protected $parent_crs_ref_id;

    public function __construct(
        int $session_ref_id,
        int $parent_crs_ref_id,
        string $title,
        string $description,
        bool $fullday,
        \DateTime $start,
        \DateTime $end
    ) {
        $this->session_ref_id = $session_ref_id;
        $this->parent_crs_ref_id = $parent_crs_ref_id;
        parent::__construct($title, $description, $fullday, $start, $end);
    }

    public function getType() : string
    {
        return static::TYPE_SESSION;
    }

    public function getSessionRefId() : int
    {
        return $this->session_ref_id;
    }

    public function getCrsRefId() : int
    {
        return $this->parent_crs_ref_id;
    }
}
