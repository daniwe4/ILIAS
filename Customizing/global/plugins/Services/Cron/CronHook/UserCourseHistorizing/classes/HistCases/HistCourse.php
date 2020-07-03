<?php

namespace CaT\Plugins\UserCourseHistorizing\HistCases;

use CaT\Historization\HistCase\HistCase as HistCase;
use CaT\Historization\Event\Event as Event;
use CaT\Plugins\UserCourseHistorizing\Digesters as Digesters;

class HistCourse implements HistCase
{
    /**
     * The title of the case, should be unique among all the cases.
     *
     * @return	string
     */
    public function title()
    {
        return 'crs';
    }

    /**
     * Get the field names describing the id of the case. The actual id
     * values are a part of the payload.
     *
     * @return	string[]
     */
    public function id()
    {
        return ['crs_id'];
    }

    /**
     * Get all the fields stored corresponding to this service.
     *
     * @return	string[]
     */
    public function fields()
    {
        return [
            'crs_id'
            ,'title'
            ,'crs_type'
            ,'topics'
            ,'deleted'
            ,'venue'
            ,'accomodation'
            ,'tut'
            ,'provider'
            ,'begin_date'
            ,'end_date'
            ,'created_ts'
            ,'creator'
            ,'edu_programme'
            ,'categories'
            ,'idd_learning_time'
            ,'is_template'
            ,'booking_dl_date'
            ,'storno_dl_date'
            ,'booking_dl'
            ,'storno_dl'
            ,'max_members'
            ,'min_members'
            ,'net_total_cost'
            ,'gross_total_cost'
            ,'costcenter_finalized'
            ,'participation_finalized_date'
            ,'accomodation_date_start'
            ,'accomodation_date_end'
            ,'fee'
            ,'to_be_acknowledged'
            ,'venue_freetext'
            ,'provider_freetext'
            ,'gti_learning_time'
            ,'max_cancellation_fee'
            ,'gti_category'
            ,'target_groups'
            ,'venue_from_course'
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
            'title'
            ,'crs_type'
            ,'topics'
            ,'deleted'
            ,'venue'
            ,'accomodation'
            ,'tut'
            ,'provider'
            ,'begin_date'
            ,'end_date'
            ,'edu_programme'
            ,'categories'
            ,'idd_learning_time'
            ,'is_template'
            ,'booking_dl_date'
            ,'storno_dl_date'
            ,'booking_dl'
            ,'storno_dl'
            ,'max_members'
            ,'min_members'
            ,'net_total_cost'
            ,'gross_total_cost'
            ,'costcenter_finalized'
            ,'participation_finalized_date'
            ,'accomodation_date_start'
            ,'accomodation_date_end'
            ,'fee'
            ,'to_be_acknowledged'
            ,'venue_freetext'
            ,'provider_freetext'
            ,'gti_learning_time'
            ,'max_cancellation_fee'
            ,'gti_category'
            ,'target_groups'
            ,'venue_from_course'
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
            case 'title':
                return self::HIST_TYPE_TEXT;
            case 'crs_type':			// should come
            case 'venue':				// from other
            case 'accomodation':
            case 'provider':			// plugins
            case 'edu_programme':
            case 'gti_category':
                return self::HIST_TYPE_STRING;
            case 'crs_id':
            case 'creator':
            case 'idd_learning_time':
            case 'booking_dl':
            case 'storno_dl':
            case 'max_members':
            case 'min_members':
            case 'gti_learning_time':
                return self::HIST_TYPE_INT;
            case 'deleted':
            case 'is_template':
            case 'costcenter_finalized':
            case 'to_be_acknowledged':
            case 'venue_freetext':
            case 'provider_freetext':
            case 'venue_from_course':
                return self::HIST_TYPE_BOOL;
            case 'created_ts':
                return self::HIST_TYPE_TIMESTAMP;
            case 'tut':
                return self::HIST_TYPE_LIST_INT;
            case 'begin_date':
            case 'end_date':
            case 'booking_dl_date':
            case 'storno_dl_date':
            case 'participation_finalized_date':
            case 'accomodation_date_start':
            case 'accomodation_date_end':
                return self::HIST_TYPE_DATE;
            case 'topics':
            case 'categories':
                return self::HIST_TYPE_LIST_STRING;
            case 'net_total_cost':
            case 'gross_total_cost':
            case 'fee':
            case 'max_cancellation_fee':
                return self::HIST_TYPE_FLOAT;
            case 'target_groups':
                return self::HIST_TYPE_LIST_STRING;
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

        return $this->relevantRepositoryObjectEvent($location, $type, $payload)

            || ($location === 'Modules/Course'
                && ($type === 'create' || $type === 'update' || $type === 'delete'))
            || ($location === 'Modules/Course'
                && $type === 'memberlist_finalized'
                && (string) $payload['crs_obj_id'] !== ''
                && (string) $payload['crs_obj_id'] !== '0')
            || ($location === 'Services/AccessControl'
                && ($type === 'assignUser' || $type === 'deassignUser')
                && $payload['type'] === 'crs' && $this->trainerRole($payload['role_id']))
            || ($location === 'Plugin/Accomodation' && (
                $type === 'updateAccomodation'
                    || $type === 'deleteAccomodation'
            ))
            || ($location === 'Plugin/CourseClassification' && ($type === 'updateCCObject' || $type === 'updateCCStatic'))
            || ($location === 'Plugin/BookingModalities' && $payload['parent_course'] !== null)
            || ($location === 'Plugin/Venue' && $type === 'updateVenueStatic')
            || ($location === 'Plugin/TrainingProvider' && $type === 'updateTrainingProviderStatic')
            || ($location === 'Plugin/EduTracking' && ($type === 'updateIDD'))
            || ($location === 'Plugin/EduTracking' && ($type === 'updateGTI'))
            || ($location === 'Plugin/CopySettings'
                && ($type === 'createCopySettings'
                    || $type === 'deleteCopySettings'
                    || $type === 'updateCopySettings')
                && $payload['parent'] !== null && $payload['parent'] instanceof \ilObjCourse)
            || ($location === 'Plugin/Accounting' && (
                $type === 'updateAccounting'
                    || $type === 'updateVatRate'
                    || $type === 'deleteAccounting'
                    || $type === 'updateFee'
                    || $type === 'updateCancellationFee'
            ) && $payload['xacc']->getParentCourse() !== null)
            || ($location === 'Plugin/WBDInterface' && $type === 'importCourseWBD')
            || ($location === 'Plugin/OnlineSeminar' && $type === 'online_seminar_finalized')
        ;
    }

    const RELEVANT_TYPE_YES = 1;
    const RELEVANT_TYPE_NO = -1;
    const RELEVANT_TYPE_IRRELEVANT_EVENT = 0;

    private function relevantRepositoryObjectEvent($location, $type, array $payload)
    {
        $relevant_type = self::RELEVANT_TYPE_IRRELEVANT_EVENT;
        if ($location === 'Services/Object' && $type === 'delete') {
            $obj_type = $payload['type'];
            if ($this->relevantROType($obj_type)) {
                $relevant_type = self::RELEVANT_TYPE_YES;
            } else {
                $relevant_type = self::RELEVANT_TYPE_NO;
            }
            $crs_search_node_id = $payload['old_parent_ref_id'];
        }
        if ($location === 'Services/Tree' && $type === 'insertNode') {
            $obj_type = \ilObject::_lookupType($payload['node_id'], true);
            if ($this->relevantROType($obj_type)) {
                $relevant_type = self::RELEVANT_TYPE_YES;
            } else {
                $relevant_type = self::RELEVANT_TYPE_NO;
            }
            $crs_search_node_id = $payload['parent_id'];
        }

        if ($relevant_type === self::RELEVANT_TYPE_YES) {
            return $this->subcourseNode($crs_search_node_id);
        }
        if ($relevant_type === self::RELEVANT_TYPE_NO) {
            global $DIC;
            $DIC['ilLog']->write(
                $obj_type . ' subjected to repo operation ' . $type
                . ' which is not relevant and will not be historized'
            );
        }
        return false;
    }


    private function relevantROType($obj_type)
    {
        if (strpos($obj_type, 'x') !== 0) {
            // certainly not a plugin
            return false;
        }
        try {
            // may be a plugin, allthough someone may be messing around
            // and introduced an object type beginning with x, which is
            // not a plugin
            $plugin = \ilPluginAdmin::getPluginObjectById($obj_type);
        } catch (\Exception $e) {
            return false;
        }
        if ($plugin instanceof \HistorizedRepositoryPlugin) {
            return in_array($this->title(), $plugin->relevantHistCases());
        }
        return false;
    }

    private function subcourseNode($ref_id)
    {
        global $DIC;
        foreach ($DIC['tree']->getPathFull($ref_id) as $node) {
            if ($node['type'] === 'crs') {
                return true;
            }
        }
        return false;
    }


    private function trainerRole($role_id)
    {
        return strpos(\ilObject::_lookupTitle($role_id), 'il_crs_tutor_') === 0;
    }

    /**
     * Add relevant digesters to a given event.
     *
     * @param	Event	$event
     * @return	Event
     */
    public function addDigesters(Event $event)
    {
        global $DIC;
        $location = $event->location();
        $type = $event->type();
        switch ($type) {
            case 'create':
                if ($location === 'Modules/Course') {
                    $event = $event->withDigester(new Digesters\TitleDigester())
                                ->withDigester(new Digesters\DeletedDigester(false))
                                ->withDigester(new Digesters\CourseDatesDigester());
                }
                break;
            case 'update':
                if ($location === 'Modules/Course') {
                    $event = $event->withDigester(new Digesters\TitleDigester())
                                ->withDigester(new Digesters\CourseDatesDigester())
                                ->withDigester(new Digesters\VenueDigester())
                                ->withDigester(new Digesters\ProviderDigester())
                                ->withDigester(new Digesters\BookingModalitiesDigester($type, $DIC["tree"], $DIC["objDefinition"]));
                } elseif ($location === 'Plugin/BookingModalities') {
                    $event = $event->withDigester(new Digesters\BookingModalitiesDigester($type, $DIC["tree"], $DIC["objDefinition"]));
                }
                break;
            case 'updateAccomodation':
                $event = $event->withDigester(new Digesters\AccomodationDigester());
                break;
            case 'deleteAccomodation':
                $event = $event->withDigester(new Digesters\AccomodationDeletedDigester());
                break;
            case 'delete':
                switch ($location) {
                    case 'Modules/Course':
                        $event = $event->withDigester(new Digesters\DeletedDigester(true));
                        break;
                    case 'Services/Object':
                        $event = $event->withDigester(new Digesters\DeleteRepoObjectDigester());
                        break;
                }
                break;
            case 'assignUser':
            case 'deassignUser':
                $event = $event->withDigester(new Digesters\TrainerDigester());
                break;
            case 'insertNode':
                $event = $event->withDigester(new Digesters\InsertRepoObjectDigester());
                break;
            case 'updateCCObject':
            case 'updateCCStatic':
                $event = $event->withDigester(new Digesters\CourseClassificationDigester($event->type()));
                break;
            case 'updateVenueStatic':
                $event = $event->withDigester(new Digesters\VenueDigester());
                break;
            case 'updateTrainingProviderStatic':
                $event = $event->withDigester(new Digesters\ProviderDigester());
                break;
            case 'createCopySettings':
            case 'deleteCopySettings':
            case 'updateCopySettings':
                $event = $event->withDigester(new Digesters\CopySettingsDigester());
                break;
            case 'memberlist_finalized':
            case 'online_seminar_finalized':
                $event = $event->withDigester(new Digesters\MemberlistFinalizedDigester());
                break;
            case 'updateAccounting':
            case 'updateVatRate':
            case 'updateFee':
            case 'updateCancellationFee':
                $event = $event->withDigester(new Digesters\AccountingModifiedDigester());
                break;
            case 'deleteAccounting':
                $event = $event->withDigester(new Digesters\AccountingDeletedDigester());
                break;
            case 'updateIDD':
                $event = $event->withDigester(new Digesters\IDDDigester());
                break;
            case 'updateGTI':
                $event = $event->withDigester(new Digesters\GTIDigester());
                break;
            case 'importCourseWBD':
                break;
        }
        return 	$event
                    ->withDigester(new Digesters\CourseIdDigester())
                    ->withDigester(new Digesters\CreatedTSDigester())
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
        return isset($case_id['crs_id']);
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
