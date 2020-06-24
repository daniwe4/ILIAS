<?php

namespace CaT\Plugins\Accomodation\ObjSettings;

/**
 * Calculations for single dates in a range.
 *
 * @author 	Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Overnights
{

    /**
     * @var ObjSettings\ObjSettings
     */
    protected $settings;

    /**
     * @var DateTime | null
     */
    protected $course_start;

    /**
     * @var DateTime | null
     */
    protected $course_end;


    public function __construct(
        ObjSettings $settings,
        ?\DateTime $course_start = null,
        ?\DateTime $course_end = null
    ) {
        $this->settings = $settings;
        $this->course_start = $course_start;
        $this->course_end = $course_end;
    }

    /**
     * Central function in this class to format the date.
     * @param 	DateTime 	$dat
     * @return 	string
     */
    protected function format(\DateTime $dat)
    {
        return $dat->format("Y-m-d");
    }

    /**
     * Get the start-date, either from course or settings.
     *
     * @return DateTime
     */
    public function getEffectiveStartDate()
    {
        if ($this->settings->getDatesByCourse()) {
            return $this->course_start;
        } else {
            return $this->settings->getStartDate();
        }
    }

    /**
     * Get the end-date, either from course or settings.
     *
     * @return DateTime
     */
    public function getEffectiveEndDate()
    {
        if ($this->settings->getDatesByCourse()) {
            return $this->course_end;
        } else {
            return $this->settings->getEndDate();
        }
    }

    /**
     * Get all overnights for the given dates, NOT including prior- and post-nights.
     * An overnight is given as date-string, and as the date of the _evening_,
     * e.g. the night from Dec, 24th to 25th 2020 will be 2020-12-24.
     *
     * @return string[]
     */
    public function getOvernightsBase()
    {
        $start = $this->getEffectiveStartDate();
        $end = $this->getEffectiveEndDate();
        if (is_null($start)) {
            return [];
        }
        $diff_days = $start->diff($end)->format('%a');
        if ($diff_days < 1) {
            return [];
        }

        $ret = [];
        $start = clone $start; //do not really modify
        for ($i = 0; $i < $diff_days; ++$i) {
            $ret[] = $this->format($start);
            $start->modify('+1 day');
        }

        return $ret;
    }

    /**
     * Get all overnights for the given dates, including prior- and post-nights.
     *
     * @return string[]
     */
    public function getOvernightsExtended()
    {
        $nights = $this->getOvernightsBase();
        array_unshift($nights, $this->getPriorNight());
        array_push($nights, $this->getPostNight());
        return $nights;
    }

    /**
     * Get the overnights for a user's view:
     * this takes prior/post-night settings into account
     *
     * @return string[]
     */
    public function getOvernightsForUser()
    {
        $nights = $this->getOvernightsBase();
        if ($this->settings->isPriorDayAllowed()) {
            array_unshift($nights, $this->getPriorNight());
        }
        if ($this->settings->isFollowingDayAllowed()) {
            array_push($nights, $this->getPostNight());
        }
        return $nights;
    }

    /**
     * Get the overnight prior to the given dates.
     *
     * @return string | null
     */
    public function getPriorNight()
    {
        $dat = $this->getEffectiveStartDate();
        if (is_null($dat)) {
            return null;
        }
        $dat = clone $dat;
        $dat = $dat->modify('-1 day');
        return $this->format($dat);
    }

    /**
     * Get the overnight following the given dates.
     *
     * @return string  | null
     */
    public function getPostNight()
    {
        $dat = $this->getEffectiveEndDate();
        if (is_null($dat)) {
            return null;
        }
        return $this->format($dat);
    }

    /**
     * Get booking deadline by substracting x days (settings) from start-date.
     *
     * @return \DateTime | null
     */
    public function getBookingDeadline()
    {
        $dat = $this->getEffectiveStartDate();
        if (is_null($dat)) {
            return null;
        }
        $dat = clone $dat;
        $modifier = sprintf(
            '-%s day',
            (string) $this->settings->getBookingEnd()
        );
        $booking_end = $dat->modify($modifier);
        return $booking_end;
    }
}
