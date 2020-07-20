<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types = 1);

namespace CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

use \ILIAS\UI\Component;

/**
 * This is a modified copy of the ilias AttendanceList
 * see: Services/Membership/classes/class.ilAttendanceList.php
 */
class TMSAttendanceList
{
    /**
     * @var ilLogger
     */
    protected $logger = null;

    protected $parent_obj; // [object]
    protected $participants; // [object]
    protected $waiting_list; // [object]
    protected $callback; // [string|array]
    protected $presets; // [array]
    protected $role_data; // [array]
    protected $roles; // [array]
    protected $has_local_role; // [bool]
    protected $blank_columns; // [array]
    protected $title; // [string]
    protected $description; // [string]
    protected $pre_blanks; // [array]
    protected $id; // [string]
    protected $include_waiting_list; // [bool]
    protected $include_subscribers;  // [bool]
    protected $user_filters; // [array]

    /**
     * @var string
     */
    protected $plugin_path;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var string | null
     */
    protected $logo_path;

    /**
     * @var Component\Factory
     */
    protected $ui_factory;

    /**
     * @var RendererInterface
     */
    protected $ui_renderer;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $main_template;

    public function __construct(\ilObjCourse $a_parent_obj, string $plugin_path, \Closure $txt, string $logo_path = null)
    {
        global $DIC;

        $this->logger = $DIC->logger()->mmbr();
        $this->lng = $DIC["lng"];
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('lp');
        $this->lng->loadLanguageModule('tms');
        $this->lng->loadLanguageModule('common');

        $this->plugin_path = $plugin_path;
        $this->txt = $txt;

        $this->parent_obj = $a_parent_obj;
        $this->participants = $a_parent_obj->getMembersObject();
        $this->waiting_list = new \ilCourseWaitingList($a_parent_obj->getId());
        $this->logo_path = $logo_path;
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->main_template = $DIC->ui()->mainTemplate();

        // always available
        $this->presets['name'] = array($DIC->language()->txt('name'), true);
        $this->presets['login'] = array($DIC->language()->txt('login'), true);

        // add exportable fields
        $this->readOrderedExportableFields();
        $this->readLPFieldsToList();

        // roles
        $roles = $this->participants->getRoles();

        foreach ($roles as $role_id) {
            $title = \ilObject::_lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_a':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_admin'), 'admin');
                    break;

                case 'il_crs_t':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_tutor'), 'tutor');
                    break;

                case 'il_crs_m':
                    $this->addRole($role_id, $DIC->language()->txt('event_tbl_member'), 'member');
                    break;

                // local
                default:
                    $this->has_local_role = true;
                    $this->addRole($role_id, $title, 'local');
                    break;
            }
        }
    }

    public function configurateByTemplate(ConfigurableListConfig $tpl_data)
    {
        $presets = array_merge(
            $tpl_data->getStandardFields(),
            $tpl_data->getUdfFields(),
            $tpl_data->getLpFields()
        );

        foreach (array_keys($this->presets) as $id) {
            $this->presets[$id][1] = in_array($id, $presets);
        }

        $this->setTitle($tpl_data->getName(), $tpl_data->getDescription());
        $this->setBlankColumns($tpl_data->getAdditionalFields());

        $roles = [];
        $selection_of_users = $tpl_data->getRoleFields();
        foreach (array_keys($this->role_data) as $role_id) {
            $title = \ilObject::_lookupTitle($role_id);
            $role_name = $role_id;
            switch (substr($title, 0, 10)) {
                case 'il_crs_adm':
                    $role_name = 'adm';
                    break;
                case 'il_crs_mem':
                    $role_name = 'mem';
                    break;
                case 'il_crs_tut':
                    $role_name = 'tut';
                    break;
            }

            if (in_array('role_' . $role_name, $selection_of_users)) {
                $roles[] = $role_id;
            }
        }
        $this->setRoleSelection($roles);

        if ($this->waiting_list) {
            $this->include_subscribers = (bool) in_array('request', $selection_of_users);
            $this->include_waiting_list = (bool) in_array('waiting', $selection_of_users);
        }
    }

    /**
     * read object export fields
     * @return boolean
     */
    protected function readOrderedExportableFields()
    {
        $field_info = \ilExportFieldsInfo::_getInstanceByType($this->parent_obj->getType());
        $field_info->sortExportFields();

        foreach ($field_info->getExportableFields() as $field) {
            switch ($field) {
                case 'username':
                case 'firstname':
                case 'lastname':
                    continue 2;
            }

            \ilLoggerFactory::getLogger('mmbr')->dump($field, \ilLogLevel::DEBUG);
            // Check if default enabled
            $this->presets[$field] = array(
                $GLOBALS['DIC']['lng']->txt($field),
                false
            );
        }

        // add udf fields
        $udf = \ilUserDefinedFields::_getInstance();
        foreach ($udf->getExportableFields($this->parent_obj->getId()) as $field_id => $udf_data) {
            $this->presets['udf_' . $field_id] = array(
                $udf_data['field_name'],
                false
            );
        }

        // add cdf fields
        $fields = \ilCourseDefinedFieldDefinition::_getFields($this->parent_obj->getId());
        foreach ($fields as $field_obj) {
            $this->presets['cdf_' . $field_obj->getId()] = array(
                $field_obj->getName(),
                false
            );
        }
        return true;
    }

    protected function readLPFieldsToList()
    {
        $show_tracking = \ilObjUserTracking::_enabledLearningProgress() &&
            \ilObjUserTracking::_enabledUserRelatedData()
        ;
        if ($show_tracking) {
            $olp = \ilObjectLP::getInstance($this->parent_obj->getId());
            $show_tracking = $olp->isActive();
        }
        if ($show_tracking) {
            $this->addPreset('progress', $this->lng->txt('learning_progress'), true);
        }

        $privacy = \ilPrivacySettings::_getInstance();
        if ($privacy->enabledAccessTimesByType($this->parent_obj->getType())) {
            $this->addPreset('access', $this->lng->txt('last_access'), true);
        }

        switch ($this->parent_obj->getType()) {
            case 'crs':
                $this->addPreset('status', $this->lng->txt('crs_status'), true);
                $this->addPreset('passed', $this->lng->txt('crs_passed'), true);
                break;
            case 'sess':
            case 'grp':
            default:
                break;
        }
    }

    /**
     * Add user field
     *
     * @param string $a_id
     * @param string $a_caption
     * @param bool $a_selected
     */
    public function addPreset($a_id, $a_caption, $a_selected = false)
    {
        $this->presets[$a_id] = array($a_caption, $a_selected);
    }

    /**
     * Add blank column preset
     *
     * @param string $a_caption
     */
    public function addBlank($a_caption)
    {
        $this->pre_blanks[] = $a_caption;
    }

    /**
     * Set titles
     *
     * @param string $a_title
     * @param string $a_description
     */
    public function setTitle($a_title, $a_description = null)
    {
        $this->title = $a_title;
        $this->description = $a_description;
    }

    /**
     * Add role
     *
     * @param int $a_id
     * @param string $a_caption
     * @param string $a_type
     */
    protected function addRole($a_id, $a_caption, $a_type)
    {
        $this->role_data[$a_id] = array($a_caption, $a_type);
    }

    /**
     * Set role selection
     *
     * @param array $a_role_ids
     */
    protected function setRoleSelection($a_role_ids)
    {
        $this->roles = $a_role_ids;
    }

    /**
     * Add user filter
     *
     * @param int $a_id
     * @param string $a_caption
     * @param bool $a_checked
     */
    public function addUserFilter($a_id, $a_caption, $a_checked = false)
    {
        $this->user_filters[$a_id] = array($a_caption, $a_checked);
    }

    /**
     * Get user data for subscribers and waiting list
     *
     * @param array &$a_res
     */
    public function addNonMemberUserData(array &$a_res)
    {
        $subscriber_ids = $this->participants->getSubscribers();
        $user_ids = $subscriber_ids;

        if ($this->waiting_list) {
            $user_ids = array_merge($user_ids, $this->waiting_list->getUserIds());
        }

        // Finally read user profile data
        $profile_data = \ilObjUser::_readUsersProfileData($user_ids);
        foreach ($profile_data as $user_id => $fields) {
            foreach ((array) $fields as $field => $value) {
                $a_res[$user_id][$field] = $value;
            }
        }

        $udf = \ilUserDefinedFields::_getInstance();

        foreach ($udf->getExportableFields($this->parent_obj->getId()) as $field_id => $udf_data) {
            foreach ($profile_data as $user_id => $field) {
                $udf_data = new \ilUserDefinedData($user_id);
                $a_res[$user_id]['udf_' . $field_id] = (string) $udf_data->get('f_' . $field_id);
            }
        }

        if (sizeof($user_ids)) {
            // object specific user data
            $cdfs = \ilCourseUserData::_getValuesByObjId($this->parent_obj->getId());

            foreach (array_unique($user_ids) as $user_id) {
                if ($tmp_obj = \ilObjectFactory::getInstanceByObjId($user_id, false)) {
                    $a_res[$user_id]['login'] = $tmp_obj->getLogin();
                    $a_res[$user_id]['name'] = $tmp_obj->getLastname() . ', ' . $tmp_obj->getFirstname();

                    if (in_array($user_id, $subscriber_ids)) {
                        $a_res[$user_id]['status'] = $this->lng->txt('crs_subscriber');
                    } else {
                        $a_res[$user_id]['status'] = $this->lng->txt('crs_waiting_list');
                    }

                    foreach ((array) $cdfs[$user_id] as $field_id => $value) {
                        $a_res[$user_id]['cdf_' . $field_id] = (string) $value;
                    }
                }
            }
        }
    }

    /**
     * Add blank columns
     *
     * @param array $a_value
     */
    public function setBlankColumns(array $a_values)
    {
        if (!implode("", $a_values)) {
            $a_values = array();
        } else {
            foreach ($a_values as $idx => $value) {
                $a_values[$idx] = trim($value);
                if ($a_values[$idx] == "") {
                    unset($a_values[$idx]);
                }
            }
        }
        $this->blank_columns = $a_values;
    }

    /**
     * Set id (used for user form settings)
     *
     * @param string $a_value
     */
    public function setId($a_value)
    {
        $this->id = (string) $a_value;
    }

    public function getFullscreenHTML(array $member_data)
    {
        $actions = $this->ui_factory->dropdown()->standard([
            $this->getPrintButton()
        ]);

        $table_html = $this->getTableHTML($member_data);
        $table_panel = $this->ui_factory->panel()->sub("", $this->ui_factory->legacy($table_html));

        $panel = $this->ui_factory->panel()->standard(
            "",
            [
                $this->getHeaderSubPanel(),
                $table_panel
            ]
        )->withActions($actions);

        $this->main_template->setTitle("");
        $tpl = new \ilTemplate('tpl.attendance_list_print.html', true, true, $this->plugin_path);
        $tpl->setVariable("PANELS", $this->ui_renderer->render($panel));
        $this->main_template->setContent($tpl->get());
        $this->main_template->printToStdout();
    }


    protected function getPrintButton() : Component\Button\Shy
    {
        $label = $this->txt("action_print");
        $button = $this->ui_factory->button()
            ->shy($label, '')
            ->withOnLoadCode(function ($id) {
                return "$('#{$id}').on('click', function(event) {
                    window.print();
                })";
            });
        return $button;
    }


    protected function getHeaderSubPanel() : Component\Panel\Sub
    {
        $content = $this->getList();
        $panel = $this->ui_factory->panel()->sub("", $content);

        if ($this->logo_path) {
            $image = $this->ui_factory->image()->responsive($this->logo_path, "");
            $card = $this->ui_factory->card()->standard("", $image);
            $panel = $panel->withCard($card);
        }

        return $panel;
    }

    protected function getList()
    {
        \ilDatePresentation::setUseRelativeDates(false);
        $time = \ilDatePresentation::formatDate(new \ilDateTime(time(), IL_CAL_UNIX));
        if ($this->description) {
            $des = $this->description . " (" . $time . ")";
        } else {
            $des = $time;
        }

        $items = [
            $this->title => '',
            $des => ''
        ];

        if ($this->parent_obj instanceof \ilObjCourse) {
            $items[$this->lng->txt("course")] = $this->parent_obj->getTitle();

            if (\ilPluginAdmin::isPluginActive('venues')) {
                $venue = '-';
                $vplug = \ilPluginAdmin::getPluginObjectById('venues');

                $vactions = $vplug->getActions();
                $vassignment = $vactions->getAssignment((int) $this->parent_obj->getId());

                if ($vassignment) {
                    if ($vassignment->isCustomAssignment()) {
                        $venue = $vassignment->getVenueText();
                    }

                    if ($vassignment->isListAssignment()) {
                        $venue_id = $vassignment->getVenueId();
                        $obj_venue = $vactions->getVenue($venue_id);
                        $venue = $obj_venue->getGeneral()->getName();
                        $address = $obj_venue->getAddress();

                        if ($address->getCity() != '') {
                            $venue .= ', ' . $address->getCity();
                        }
                    }
                }

                $items[$this->txt("sig_venue")] = $venue;
            }

            $start_date = $this->parent_obj->getCourseStart();
            $end_date = $this->parent_obj->getCourseEnd();
            if (!is_null($start_date)) {
                $duration = \ilDatePresentation::formatDate($start_date) .
                    " - " .
                    \ilDatePresentation::formatDate($end_date)
                ;
                $items[$this->lng->txt("date")] = $duration;
            }
            $tutors = $this->parent_obj->getMembersObject()->getTutors();
            $tutor_names = [];
            foreach ($tutors as $tutor) {
                $info = \ilObjUser::_lookupName($tutor);
                $tutor_names[] = $info["firstname"] . " " . $info["lastname"];
            }
            if (count($tutor_names) > 0) {
                $items[$this->lng->txt("trainer")] = join(", ", $tutor_names);
            }
            $items[$this->txt("sig_tutor")] = "";
        }

        return $this->ui_factory->listing()->characteristicValue()->text($items);
    }

    protected function getTableHTML(array $member_data)
    {
        $tpl = new \ilTemplate('tpl.attendance_list_table.html', true, true, $this->plugin_path);

        \ilLoggerFactory::getLogger('mmbr')->dump($this->presets, \ilLogLevel::DEBUG);

        $tpl->setCurrentBlock('head_item');
        foreach ($this->presets as $id => $item) {
            if ($item[1]) {
                $tpl->setVariable('TXT_HEAD', $item[0]);
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->blank_columns) {
            foreach ($this->blank_columns as $blank) {
                $tpl->setVariable('TXT_HEAD', $blank);
                $tpl->parseCurrentBlock();
            }
        }

        // handle members
        $valid_user_ids = $filters = array();

        if ($this->roles) {
            if ($this->has_local_role) {
                $members = array();
                foreach ($this->participants->getMembers() as $member_id) {
                    foreach ($this->participants->getAssignedRoles($member_id) as $role_id) {
                        $members[$role_id][] = $member_id;
                    }
                }
            } else {
                $members = $this->participants->getMembers();
            }

            foreach ($this->roles as $role_id) {
                switch ($this->role_data[$role_id][1]) {
                    case "admin":
                        $valid_user_ids = array_merge($valid_user_ids, $this->participants->getAdmins());
                        break;
                    case "tutor":
                        $valid_user_ids = array_merge($valid_user_ids, $this->participants->getTutors());
                        break;
                    // member/local
                    default:
                        if (!$this->has_local_role) {
                            $valid_user_ids = array_merge($valid_user_ids, (array) $members);
                        } else {
                            $valid_user_ids = array_merge($valid_user_ids, (array) $members[$role_id]);
                        }
                        break;
                }
            }
        }

        if ($this->include_subscribers) {
            $valid_user_ids = array_merge($valid_user_ids, $this->participants->getSubscribers());
        }

        if ($this->include_waiting_list) {
            $valid_user_ids = array_merge($valid_user_ids, $this->waiting_list->getUserIds());
        }

        if ($this->user_filters) {
            foreach ($this->user_filters as $sub_id => $sub_item) {
                $filters[$sub_id] = (bool) $sub_item[2];
            }
        }

        $valid_user_ids = \ilUtil::_sortIds(array_unique($valid_user_ids), 'usr_data', 'lastname', 'usr_id');
        foreach ($valid_user_ids as $user_id) {
            if (array_key_exists($user_id, $member_data)) {
                $user_data = $member_data[$user_id];

                $tpl->setCurrentBlock("row_preset");
                foreach ($this->presets as $id => $item) {
                    if ($item[1] === true) {
                        switch ($id) {
                            case "name":
                                if (!$user_data[$id]) {
                                    $name = \ilObjUser::_lookupName($user_id);
                                    $user_data[$id] = $name["lastname"] . ", " . $name["firstname"];
                                }
                                break;
                            case "login":
                                if (!$user_data[$id]) {
                                    $user_data[$id] = \ilObjUser::_lookupLogin($user_id);
                                }
                                break;
                        }
                        $value = (string) $user_data[$id];
                        $tpl->setVariable("TXT_PRESET", (string) $value);
                        $tpl->parseCurrentBlock();
                    }
                }
            }

            if ($this->blank_columns) {
                for ($loop = 0; $loop < sizeof($this->blank_columns); $loop++) {
                    $tpl->touchBlock('row_blank');
                }
            }
            $tpl->touchBlock("member_row");
        }

        return $tpl->get();
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
