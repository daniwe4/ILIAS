<?php

namespace CaT\Plugins\WBDCrsHistorizing\HistCases;

use CaT\Historization\HistCase\HistCase as HistCase;
use CaT\Historization\Event\Event as Event;
use CaT\Plugins\WBDCrsHistorizing\Digesters as Digesters;

class WBDCrs implements HistCase
{
    /**
     * The title of the case, should be unique among all the cases.
     *
     * @return	string
     */
    public function title()
    {
        return 'wbd_crs';
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
            ,'created_ts'
            ,'creator'
            ,'internal_id'
            ,'wbd_learning_type'
            ,'wbd_learning_content'
            ,'contact_title_tutor'
            ,'contact_firstname_tutor'
            ,'contact_lastname_tutor'
            ,'contact_email_tutor'
            ,'contact_phone_tutor'
            ,'contact_title_admin'
            ,'contact_firstname_admin'
            ,'contact_lastname_admin'
            ,'contact_email_admin'
            ,'contact_phone_admin'
            ,'contact_title_xccl'
            ,'contact_firstname_xccl'
            ,'contact_lastname_xccl'
            ,'contact_email_xccl'
            ,'contact_phone_xccl'
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
            'internal_id'
            ,'wbd_learning_type'
            ,'wbd_learning_content'
            ,'contact_title_tutor'
            ,'contact_firstname_tutor'
            ,'contact_lastname_tutor'
            ,'contact_email_tutor'
            ,'contact_phone_tutor'
            ,'contact_title_admin'
            ,'contact_firstname_admin'
            ,'contact_lastname_admin'
            ,'contact_email_admin'
            ,'contact_phone_admin'
            ,'contact_title_xccl'
            ,'contact_firstname_xccl'
            ,'contact_lastname_xccl'
            ,'contact_email_xccl'
            ,'contact_phone_xccl'
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
            case 'creator':
                return self::HIST_TYPE_INT;
            case 'created_ts':
                return self::HIST_TYPE_TIMESTAMP;
            case 'wbd_learning_type':
            case 'wbd_learning_content':
            case 'internal_id':
            case 'contact_title_tutor':
            case 'contact_firstname_tutor':
            case 'contact_lastname_tutor':
            case 'contact_email_tutor':
            case 'contact_phone_tutor':
            case 'contact_title_admin':
            case 'contact_firstname_admin':
            case 'contact_lastname_admin':
            case 'contact_email_admin':
            case 'contact_phone_admin':
            case 'contact_title_xccl':
            case 'contact_firstname_xccl':
            case 'contact_lastname_xccl':
            case 'contact_email_xccl':
            case 'contact_phone_xccl':
                return self::HIST_TYPE_TEXT;
            case 'deleted':
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

        return  $this->relevantRepositoryObjectEvent($location, $type, $payload)
            || ($location === 'Services/AccessControl'
                && ($type === 'assignUser' || $type === 'deassignUser')
                && $payload['type'] === 'crs' && $this->relevantRole($payload['role_id']))
            || ($location === 'Plugin/CourseClassification' && $type === 'updateCCObject')
            || ($location === 'Plugin/EduTracking' && $type === 'updateWBD')
            || ($location === 'Plugin/WBDInterface' && $type === 'importCourseWBD');
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


    private function relevantSubcourseNode($node_id, $parent_id)
    {
        if (in_array(\ilObject::_lookupType($node_id, true), self::$relevant_subcourse_types)) {
            global $DIC;
            foreach ($DIC['tree']->getNodePath($node_id) as $node) {
                if ($node['type'] === 'crs') {
                    return true;
                }
            }
        }
        return false;
    }

    private function relevantRole($role_id)
    {
        $role_title = \ilObject::_lookupTitle($role_id);
        return strpos($role_title, 'il_crs_tutor_') === 0
            || strpos($role_title, 'il_crs_admin_') === 0;
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
                break;
            case 'delete':
                switch ($location) {
                    case 'Modules/Course':
                        $event = $event->withDigester(new Digesters\DeletedDigester());
                        break;
                    case 'Services/Object':
                        $event = $event->withDigester(new Digesters\DeleteRepoObjectDigester());
                        break;
                }
                break;
            case 'assignUser':
            case 'deassignUser':
                $event = $event->withDigester(new Digesters\CrsRoleAssignmentDigester());
                break;
            case 'insertNode':
                $event = $event->withDigester(new Digesters\InsertRepoObjectDigester());
                break;
            case 'updateCCObject':
                $event = $event->withDigester(new Digesters\CourseClassificationDigester($event->type()));
                break;
            case 'updateWBD':
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
