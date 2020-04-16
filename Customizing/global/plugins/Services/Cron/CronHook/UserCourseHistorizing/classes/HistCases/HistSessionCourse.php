<?php

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use CaT\Historization\HistCase\HistCase as HistCase;
use CaT\Historization\Event\Event as Event;
use CaT\Plugins\UserCourseHistorizing\Digesters as Digesters;

class HistSessionCourse implements HistCase
{


    /**
     * The title of the case, should be unique among all the cases.
     *
     * @return	string
     */
    public function title()
    {
        return 'sesscrs';
    }

    /**
     * Get the field names describing the id of the case. The actual id
     * values are a part of the payload.
     *
     * @return	string[]
     */
    public function id()
    {
        return ['crs_id','session_id'];
    }

    /**
     * Get all the fields stored corresponding to this service.
     *
     * @return	string[]
     */
    public function fields()
    {
        return [
            'session_id',
            'crs_id',
            'begin_date',
            'end_date',
            'start_time',
            'end_time',
            'created_ts',
            'creator',
            'fullday',
            'removed'
        ];
    }

    /**
     * Get payload fields only. Skip timestamp, created user, id.
     *
     * @return	string[]
     */
    public function payloadFields()
    {
        return [
            'begin_date',
            'end_date',
            'start_time',
            'end_time',
            'fullday',
            'removed'
        ];
    }

    /**
     * Get the format of any field.
     *
     * @return	string
     */
    public function typeOfField($field)
    {
        switch ($field) {
            case 'crs_id':
            case 'session_id':
            case 'creator':
                return self::HIST_TYPE_INT;
            case 'created_ts':
            case 'start_time':
            case 'end_time':
                return self::HIST_TYPE_TIMESTAMP;
            case 'begin_date':
            case 'end_date':
                return self::HIST_TYPE_DATE;
            case 'fullday':
            case 'removed':
                return self::HIST_TYPE_BOOL;
        }
    }

    /**
     * Check wether an event is relevant for this case.
     *
     * @return	bool
     */
    public function isEventRelevant(Event $event)
    {
        $location = $event->location();
        $type = $event->type();
        $payload = $event->payload();
        return (
            $location === 'Modules/Session' &&
            ($type === 'update' || false) &&
            $this->inCourse($payload['object']->getRefId())
        ) || (
            $location === 'Services/Tree' &&
            $type === 'insertNode' &&
            \ilObject::_lookupType($payload['node_id'], true) === 'sess' &&
            $this->inCourse($payload['parent_id'])
        ) || (
            $location === 'Services/Tree' &&
            $type === 'moveTree' &&
            \ilObject::_lookupType($payload['source_id'], true) === 'sess' &&
            $this->inCourse($payload['old_parent_id'])
        ) || (
            $location === 'Services/Object' &&
            $type === 'beforeDeletion' &&
            $payload['object']->getType() === 'sess' &&
            $this->inCourse($payload['object']->getRefId())
        );
    }

    protected function inCourse($ref_id)
    {
        if (is_numeric($ref_id)) {
            global $DIC;
            foreach ($DIC['tree']->getPathFull($ref_id) as $node) {
                if ($node['type'] === 'crs') {
                    return true;
                }
                if ($node['type'] === 'grp') {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Add relevant digesters to a given event.
     *
     * @param	Event	$event
     * @return	Event
     */
    public function addDigesters(Event $event)
    {
        switch ($event->type()) {
            case 'beforeDeletion':
                $event = $event->withDigester(new Digesters\DeleteSessionDigester());
                break;
            case 'moveTree':
                $event = $event->withDigester(new Digesters\MoveSessionDigester());
                break;
            case 'insertNode':
                $event = $event->withDigester(new Digesters\InsertSessionDigester());
                break;
            case 'update':
                $event = $event->withDigester(new Digesters\UpdateSessionDigester());
                break;
        }
        return $event->withDigester(new Digesters\CreatedTSDigester())
                    ->withDigester(new Digesters\CreatorDigester());
    }

    /**
     * Get to know, wether this case uses a buffer.
     *
     * @return	bool
     */
    public function isBuffered()
    {
        return true;
    }

    /**
     * Fields relevant for case tracking.
     *
     * @return	string
     */
    public function creatorField()
    {
        return 'creator';
    }

    public function timestampField()
    {
        return 'created_ts';
    }

    /**
     * Are all relevant case id fields defined in the argument?
     *
     * @param	string|int[string]	$case_id
     * @return	bool
     */
    public function caseIdComplete(array $case_id)
    {
        return isset($case_id['crs_id']) && isset($case_id['session_id']);
    }

    /**
     * Sometimes a value may not be set at a case,
     * in opposite to not being provided, since not relevant
     * at current change. One needs a proper notation for the
     * former to distinguish from the latter, which will allways
     * be denoted as null.
     *
     * @param	string	$type
     * @return	mixed
     */
    public function noValueEntryForFieldType($type)
    {
        return null;
    }
}
