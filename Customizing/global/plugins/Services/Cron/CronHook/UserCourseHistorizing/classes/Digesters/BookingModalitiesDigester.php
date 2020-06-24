<?php

namespace CaT\Plugins\UserCourseHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class BookingModalitiesDigester implements Digester
{
    protected $type;

    /**
     * @var	\ilTree
     */
    protected $tree;

    /**
     * @var \ilObjectDefinition
     */
    protected $obj_def;

    public function __construct(string $type, \ilTree $tree, \ilObjectDefinition $obj_def)
    {
        $this->type = $type;
        $this->tree = $tree;
        $this->obj_def = $obj_def;
    }

    public function digest(array $payload) : array
    {
        $digested = [];
        if ($payload['object'] instanceof \ilObjCourse) {
            $crs = $payload['object'];
            $crs_ref = $crs->getRefId();
            if (!$crs_ref) {
                $refs = \ilObject::_getAllReferences($crs->getId());
                if (!$refs) {
                    return $digested;
                } else {
                    $crs_ref = array_shift($refs);
                }
            }

            $bm_s = $this->getFirstChildOfByType($crs_ref, 'xbkm');
        } else {
            $bm_s = $payload['object'];
            $crs = $payload['parent_course'];
        }

        if ($bm_s && $crs) {
            $digested = $this->digestByTypeAndObjects($this->type, $bm_s, $crs);
        }
        return $digested;
    }

    /**
     * Using quite soft typehinting for booking-modalities to avoid strong
     * coupling to corresponding plugin. It is assumed that the object arriving here
     * actually is a booking modalities instance. Otherwise the method-calls (getStorno etc.)
     * will fail and this will not go undetected.
     */
    protected function digestByTypeAndObjects(string $type, \ilObject $bm_s, \ilObjCourse $crs)
    {
        $return = [];
        $start_date = $this->getCourseStart($crs);
        switch ($type) {
            case 'create':
            case 'update':
                $booking_dl = (int) $bm_s->getBooking()->getDeadline();
                $storno_dl = (int) $bm_s->getStorno()->getDeadline();

                $members = $bm_s->getMember();
                $max_members = (int) $members->getMax();
                $min_members = (int) $members->getMin();

                $bookings = $bm_s->getBooking();
                return
                    ['booking_dl_date' => $this->dateMinusDays($start_date, $booking_dl)
                    ,'storno_dl_date' => $this->dateMinusDays($start_date, $storno_dl)
                    ,'booking_dl' => $booking_dl
                    ,'storno_dl' => $storno_dl
                    ,'max_members' => $max_members
                    ,'min_members' => $min_members
                    ,'crs_id' => $crs->getId()
                    ,'to_be_acknowledged' => $bookings->getToBeAcknowledged()
                    ];
                break;

            case 'delete':
                $subs = $crs->getSubItems()['xbkm'];
                if (count($subs) > 0) {
                    return $this->digestByTypeAndObjects('update', \ilObjectFactory::getInstanceByRefId(array_shift($subs)['ref_id']), $crs);
                }
                return
                    ['booking_dl_date' => $start_date
                    ,'storno_dl_date' => $start_date
                    ,'booking_dl' => 0
                    ,'storno_dl' => 0
                    ,'max_members' => 0
                    ,'min_members' => 0
                    ,'crs_id' => $crs->getId()
                    ,'to_be_acknowledged' => false
                    ];
                break;
        }
    }

    protected function dateMinusDays(string $start_date, int $days)
    {
        if ($start_date === '0001-01-01') {
            return $start_date;
        }
        return \DateTime::createFromFormat('Y-m-d', $start_date)
                    ->sub(new \DateInterval('P' . $days . 'D'))
                    ->format('Y-m-d');
    }

    protected function getCourseStart(\ilObjCourse $crs)
    {
        $crs_start = $crs->getCourseStart();
        if ($crs_start instanceof \ilDate) {
            return $crs_start->get(IL_CAL_DATE);
        }
        return '0001-01-01';
    }

    /**
     * Get first child by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * TODO: dedup with ilCourseTemplateDB and CourseCreation/Process
     *
     * @return Object 	of search type
     */
    protected function getFirstChildOfByType(int $ref_id, string $search_type)
    {
        $childs = $this->tree->getChilds($ref_id);
        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                return \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($this->obj_def->isContainer($type)) {
                $ret = $this->getFirstChildOfByType($child["child"], $search_type);
                if (!is_null($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }
}
