<?php

namespace CaT\Plugins\MaterialList;

/**
 * ilObjectActions just for actions of repository object guis
 */
class ilObjectActions
{
    const F_SETTINGS_TITLE = "title";
    const F_SETTINGS_DESCRIPTION = "description";

    const F_LIST_ENTRY_ID = "id";
    const F_LIST_ENTRY_NUMPER_PER_PARTICIPANT = "number_per_participant";
    const F_LIST_ENTRY_NUMBER_PER_COURSE = "number_per_course";
    const F_LIST_ENTRY_TITLE = "title";
    const F_LIST_ENTRY_NEW_LINE = "new_entries";
    const F_LIST_ENTRY_TO_DELETE_IDS = "to_delete_ids";
    const F_LIST_ENTRY_HIDDEN_IDS = "hidden_ids";
    const F_RECIPIENT_MODE = "recipient_mode";
    const F_RECIPIENT = "recipient";
    const F_SEND_DAYS_BEFORE = "send_days_before";

    const M_COURSE_VENUE = "course_venue";
    const M_SELECTION = "selection";

    const MAX_ARTICLE_NUMBER_LENGTH = 32;
    const MAX_TITLE_LENGTH = 256;
    const MAX_NUMBER_PER_PARTICIPANT = 10;

    const MATERIAL_NUMBER_NEW_ROWS = 12;

    /**
     * @var \ilObjMaterialList
     */
    protected $object;

    public function __construct(
        \ilObjMaterialList $object,
        \CaT\Plugins\MaterialList\Settings\DB $settings_db,
        \CaT\Plugins\MaterialList\Lists\DB $lists_db,
        \ilObjUser $user,
        \ilAppEventHandler $app_event_handler,
        \ilTree $tree,
        \ilAccessHandler $access
    ) {
        $this->object = $object;
        $this->settings_db = $settings_db;
        $this->lists_db = $lists_db;
        $this->user = $user;
        $this->app_event_handler = $app_event_handler;
        $this->tree = $tree;
        $this->access = $access;
    }

    /**
     * Get the current object
     *
     * @throws \LogigException if no object is set
     * @return \ilObjMaterialList
     */
    public function getObject()
    {
        if ($this->object === null) {
            throw new \LogicException(__METHOD__ . "/no current object is set.");
        }

        return $this->object;
    }

    /*****************************************************
    ******************************************************
    ****************     SETTINGS     ********************
    ******************************************************
    *****************************************************/

    /**
     * Create extended settings for current object
     *
     * @return null
     */
    public function createExtendedSettingsForCurrentObject()
    {
        $obj_id = $this->getObject()->getId();
        $datetime = new \ilDateTime(time(), IL_CAL_UNIX);
        $user_id = $this->user->getId();
        $recipient_mode = self::M_COURSE_VENUE;

        $this->settings_db->create((int) $obj_id, $datetime, (int) $user_id, $recipient_mode);
    }

    /**
     * Get extended settings for current object
     *
     * @return Settings\MaterialList
     */
    public function getExtendedSettingsForCurrentObject()
    {
        return $this->settings_db->selectFor((int) $this->getObject()->getId());
    }

    /**
     * Delete extended settings for current object
     *
     * @return null
     */
    public function deleteExtendedSettingsForCurrentObject()
    {
        $this->settings_db->deleteFor((int) $this->getObject()->getId());
    }

    /**
     * Update extended settings
     *
     * @param Settings\MaterialList 	$material_list
     *
     * @return null
     */
    public function updateExtendedSettings(Settings\MaterialList $material_list)
    {
        $this->settings_db->update($material_list);
    }

    /**
     * Update last edit values of current object
     *
     * @return null
     */
    public function updateLastEditValuesOfCurrentObject()
    {
        $obj = $this->getObject();

        $datetime = new \ilDateTime(time(), IL_CAL_UNIX);
        $user_id = $this->user->getId();
        $obj->updateSettings(function ($s) use ($datetime, $user_id) {
            return $s
                ->withLastEditDateTime($datetime)
                ->withLastEditBy((int) $user_id)
                ;
        });

        $obj->update();
    }

    /**
     * Update the settings of current object
     *
     * @param array $post
     *
     * @return null
     */
    public function updateSettings(array $post)
    {
        $obj = $this->getObject();
        $obj->setTitle($post[self::F_SETTINGS_TITLE]);
        $obj->setDescription($post[self::F_SETTINGS_DESCRIPTION]);

        $datetime = new \ilDateTime(time(), IL_CAL_UNIX);
        $user_id = $this->user->getId();
        $recipient_mode = $post[self::F_RECIPIENT_MODE];
        $recipient = $post[self::F_RECIPIENT];
        $send_days_before = $post[self::F_SEND_DAYS_BEFORE];

        if ($recipient_mode == self::M_COURSE_VENUE) {
            $recipient = null;
            $send_days_before = null;
        } else {
            $send_days_before = (int) $send_days_before;
        }

        $obj->updateSettings(function ($s) use ($datetime, $user_id, $recipient_mode, $recipient, $send_days_before) {
            return $s
                ->withRecipientMode($recipient_mode)
                ->withRecipient($recipient)
                ->withSendDaysBefore($send_days_before)
                ->withLastEditDateTime($datetime)
                ->withLastEditBy((int) $user_id)
                ;
        });

        $obj->update();
    }

    /**
     * Get values for settings form
     *
     * @return string[]
     */
    public function getSettingsValues()
    {
        $ret = array();
        $obj = $this->getObject();

        $ret[self::F_SETTINGS_TITLE] = $obj->getTitle();
        $ret[self::F_SETTINGS_DESCRIPTION] = $obj->getDescription();

        $settings = $obj->getSettings();
        $recipient_mode = $settings->getRecipientMode();
        if ($recipient_mode === null) {
            $recipient_mode = self::M_COURSE_VENUE;
        }
        $ret[self::F_RECIPIENT_MODE] = $recipient_mode;
        $ret[self::F_RECIPIENT] = $settings->getRecipient();
        $ret[self::F_SEND_DAYS_BEFORE] = $settings->getSendDaysBefore();

        return $ret;
    }

    public function getDefaultRecipientMode()
    {
        return self::M_COURSE_VENUE;
    }

    /*****************************************************
    ******************************************************
    ******************      Lists     ********************
    ******************************************************
    *****************************************************/

    /**
     * Get current list entries for obj
     *
     * @return Lists\ListEntry[] | []
     */
    public function getListEntiesForCurrentObj()
    {
        return $this->lists_db->selectForObjId((int) $this->getObject()->getId());
    }

    /**
     * Save list entries
     *
     * @param array 	$save_objects
     *
     * @return string[]
     */
    public function saveListEntries(array $save_objects)
    {
        $obj_id = $this->getObject()->getId();

        if (count($save_objects)) {
            foreach ($save_objects as $key => $object) {
                if ((int) $object->getId() == -1) {
                    $this->createListEntry(
                        (int) $obj_id,
                        (int) $object->getNumberPerParticipant(),
                        (int) $object->getNumberPerCourse(),
                        $object->getArticleNumber(),
                        $object->getTitle()
                    );
                }

                if ((int) $object->getId() > 0) {
                    $this->updateListEntry($object);
                }

                $counter++;
            }
        }

        return $not_saved;
    }

    /**
     * Create ne list entry
     *
     * @param int 		$obj_id
     * @param int 		$number_per_participant
     * @param int 		$number_per_course
     * @param string 	$article_number
     * @param string 	$title
     *
     * @return null
     */
    public function createListEntry(
        int $obj_id,
        int $number_per_participant,
        int $number_per_course,
        string $article_number,
        string $title
    ) {
        $this->lists_db->create($obj_id, $number_per_participant, $number_per_course, $article_number, $title);
    }

    /**
     * Update a single List entry
     *
     * @param Lists\ListEntry 		$list_entry
     *
     * @return null
     */
    public function updateListEntry(Lists\ListEntry $list_entry)
    {
        $this->lists_db->update($list_entry);
    }

    /**
     * Delete seelcted list entries
     *
     * @param array 	$post
     *
     * @return null
     */
    public function deleteListEntries(array $post)
    {
        $ids = $post[self::F_LIST_ENTRY_TO_DELETE_IDS];

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $this->deleteListEntry((int) $id);
            }
        }
    }

    /**
     * Delete a list entry
     *
     * @param int 	$id
     *
     * @return null
     */
    public function deleteListEntry(int $id)
    {
        $this->lists_db->deleteId($id);
    }

    /**
     * Delete list entries for current object
     *
     * @return null
     */
    public function deleteListEntriesForCurrentObj()
    {
        $this->lists_db->deleteForObjId((int) $this->getObject()->getId());
    }

    public function getCheckObject(
        $id,
        $number_per_participant,
        $number_per_course,
        $article_number,
        $title
    ) {
        return new Lists\CheckObject(
            $id,
            (int) $this->getObject()->getId(),
            $number_per_participant,
            $number_per_course,
            $article_number,
            $title
        );
    }

    public function getListEntry(
        int $id,
        int $number_per_participant,
        int $number_per_course,
        string $article_number,
        string $title
    ) {
        return new Lists\ListEntry(
            $id,
            (int) $this->getObject()->getId(),
            $number_per_participant,
            $number_per_course,
            $article_number,
            $title
        );
    }

    /**
     * Get a new list entry
     *
     * @return Lists\CheckObject
     */
    public function getNewCheckObject()
    {
        return new Lists\CheckObject(
            -1,
            (int) $this->getObject()->getId(),
            "",
            "",
            "",
            ""
        );
    }

    /**
     * Get article number and title by id
     *
     * @param int 	$id
     *
     * @return string
     */
    public function getArtTitleById(int $id)
    {
        return $this->lists_db->getArtTitleById($id);
    }

    /**
     * Raise update event
     *
     * @return null
     */
    public function updateEvent()
    {
        $parent_course = $this->getObject()->getParentCourse();
        $ref_id = null;
        if ($parent_course !== null) {
            $ref_id = $parent_course->getRefId();
        }
        $this->app_event_handler->raise("Plugins/MaterialList", "updated", array(
            "crs_ref_id" => $ref_id,
            "mat_ref_id" => $this->getObject()->getRefid()
            ));
    }

    /**
     * Raise delete event
     *
     * @return null
     */
    public function deleteEvent()
    {
        $parent_course = $this->getObject()->getParentCourse();
        $ref_id = null;
        if ($parent_course !== null) {
            $ref_id = $parent_course->getRefId();
        }
        $this->app_event_handler->raise("Plugins/MaterialList", "deleted", array(
            "crs_ref_id" => $ref_id,
            "mat_ref_id" => $this->getObject()->getRefid()
            ));
    }

    /**
     * Get materials for table in excel export
     *
     * @return [string[]]
     */
    public function getMaterialsForExport()
    {
        $ret = array();
        $entries = $this->getListEntiesForCurrentObj();
        $members_of_course = $this->getObject()->getParentCourse()->getMembersObject()->getCountMembers();

        foreach ($entries as $entry) {
            $element = array($entry->getArticleNumber(),
                             $entry->getTitle(),
                             (($entry->getNumberPerParticipant() * $members_of_course) + $entry->getNumberPerCourse())
                        );

            $ret[] = $element;
        }

        return $ret;
    }

    /**
     * Get all materiallists of parent course
     *
     * @return \ilObjMaterialList[]
     */
    public function getMaterialListOfParentCourse()
    {
        $ref_id = (int) $this->getObject()->getParentCourse()->getRefId();
        return $this->getObject()->getAllChildrenOfByType($ref_id, "xmat");
    }
}
