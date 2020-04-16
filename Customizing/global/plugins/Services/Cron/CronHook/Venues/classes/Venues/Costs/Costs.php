<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Costs;

/**
 * Venue configuration entries for contact settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Costs
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Fixed rate per day
     *
     * @var float | null
     */
    protected $fixed_rate_day;

    /**
     * Fixed rate all inclusive
     *
     * @var float | null
     */
    protected $fixed_rate_all_inclusive;

    /**
     * Bed and breakfast
     *
     * @var float | null
     */
    protected $bed_and_breakfast;

    /**
     * Bed
     *
     * @var float | null
     */
    protected $bed;

    /**
     * Fixed rate per conference
     *
     * @var float | null
     */
    protected $fixed_rate_conference;

    /**
     * Room usage
     *
     * @var float | null
     */
    protected $room_usage;

    /**
     * @var float | null
     */
    protected $other;

    /**
     * @var string
     */
    protected $terms;

    public function __construct(
        int $id,
        float $fixed_rate_day = null,
        float $fixed_rate_all_inclusive = null,
        float $bed_and_breakfast = null,
        float $bed = null,
        float $fixed_rate_conference = null,
        float $room_usage = null,
        float $other = null,
        string $terms = ""
    ) {
        $this->id = $id;
        $this->fixed_rate_day = $fixed_rate_day;
        $this->fixed_rate_all_inclusive = $fixed_rate_all_inclusive;
        $this->bed_and_breakfast = $bed_and_breakfast;
        $this->bed = $bed;
        $this->fixed_rate_conference = $fixed_rate_conference;
        $this->room_usage = $room_usage;
        $this->other = $other;
        $this->terms = $terms;
    }

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return float | null
     */
    public function getFixedRateDay()
    {
        return $this->fixed_rate_day;
    }

    /**
     * @return float | null
     */
    public function getFixedRateAllInclusiv()
    {
        return $this->fixed_rate_all_inclusive;
    }

    /**
     * @return float | null
     */
    public function getBedAndBreakfast()
    {
        return $this->bed_and_breakfast;
    }

    /**
     * @return float | null
     */
    public function getBed()
    {
        return $this->bed;
    }

    /**
     * @return float | null
     */
    public function getFixedRateConference()
    {
        return $this->fixed_rate_conference;
    }

    /**
     * @return float | null
     */
    public function getRoomUsage()
    {
        return $this->room_usage;
    }

    /**
     * @return float | null
     */
    public function getOther()
    {
        return $this->other;
    }

    public function getTerms() : string
    {
        return $this->terms;
    }
}
