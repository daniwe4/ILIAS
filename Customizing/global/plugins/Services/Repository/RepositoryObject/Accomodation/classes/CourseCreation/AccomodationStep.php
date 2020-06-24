<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\Accomodation\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to show user informations about accomodation and configure the venue
 */
class AccomodationStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_VENUE_SOURCE = "venue_source";
    const F_VENUE_FROM_COURSE = "venue_from_course";
    const F_VENUE_FROM_SELECTION = "venue_from_selection";
    const F_VENUE = "venue";
    const F_DATE_SOURCE = "date_source";
    const F_DATE_FROM_COURSE = "date_source_course";
    const F_DATE_FROM_SETTINGS = "date_source_settings";
    const F_STARTDATE = "startdate";
    const F_ENDDATE = "enddate";
    const F_PRIOR = "prior_day";
    const F_FOLLOWING = "following_day";
    const F_DEADLINE = "deadline";
    const F_EDIT_NOTES = "edit_notes";
    const splitter = "#:#";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \Closure $txt, $actions)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->actions = $actions;
        $this->data_appended = false;
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("accomodation");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("accomodation_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $accomodations = $this->getAccomodations();
        $this->addAccomodationInfos($form, $accomodations);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if ($this->data_appended) {
            return;
        }
        if (count($data) > 0) {
            $values = array();
            foreach ($data as $acco_id => $value) {
                $values[$acco_id . self::splitter . self::F_DATE_SOURCE] = $value[self::F_DATE_SOURCE];
                $values[$acco_id . self::splitter . self::F_STARTDATE] = $value[self::F_STARTDATE];
                $values[$acco_id . self::splitter . self::F_ENDDATE] = $value[self::F_ENDDATE];

                $values[$acco_id . self::splitter . self::F_VENUE_SOURCE] = $value[self::F_VENUE_SOURCE];
                $values[$acco_id . self::splitter . self::F_VENUE] = $value[self::F_VENUE];

                $values[$acco_id . self::splitter . self::F_PRIOR] = $value[self::F_PRIOR];
                $values[$acco_id . self::splitter . self::F_FOLLOWING] = $value[self::F_FOLLOWING];
                $values[$acco_id . self::splitter . self::F_DEADLINE] = $value[self::F_DEADLINE];
                $values[$acco_id . self::splitter . self::F_EDIT_NOTES] = $value[self::F_EDIT_NOTES];
            }
            $form->setValuesByArray($values);
            $this->data_appended = true;
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        foreach ($data as $acco_id => $value) {
            $sec = new \ilFormSectionHeaderGUI();
            $obj_id = \ilObject::_lookupObjId($acco_id);
            $sec->setTitle(\ilObject::_lookupTitle($obj_id));
            $form->addItem($sec);


            $item_date = new \ilNonEditableValueGUI($this->txt("settings_date_source"), "", true);
            if ($value[self::F_DATE_SOURCE] == self::F_DATE_FROM_SETTINGS) {
                $start = \DateTime::createFromFormat("Y-m-d", $value[self::F_STARTDATE]);
                $end = \DateTime::createFromFormat("Y-m-d", $value[self::F_ENDDATE]);
                $val_date = $start->format("d.m.Y") . ' - ' . $end->format("d.m.Y");
            } else {
                $val_date = $this->txt("settings_date_source_course");
            }
            $item_date->setValue($val_date);
            $form->addItem($item_date);

            $venue_title = $this->txt("settings_venue_source_course");
            if ($value[self::F_VENUE_SOURCE] == self::F_VENUE_FROM_SELECTION) {
                $venue = $this->actions->getVenueById((int) $value[self::F_VENUE]);
                $venue_title = $venue->getName();
            }
            $item = new \ilNonEditableValueGUI($this->txt("settings_venue_source"), "", true);
            $item->setValue($venue_title);
            $form->addItem($item);


            $item = new \ilNonEditableValueGUI($this->txt("settings_prior_day"), "", true);
            $val = $this->txt("no");
            if ($value[self::F_PRIOR]) {
                $val = $this->txt("yes");
            }
            $item->setValue($val);
            $form->addItem($item);

            $item = new \ilNonEditableValueGUI($this->txt("settings_following_day"), "", true);
            $val = $this->txt("no");
            if ($value[self::F_FOLLOWING]) {
                $val = $this->txt("yes");
            }
            $item->setValue($val);
            $form->addItem($item);

            $item = new \ilNonEditableValueGUI($this->txt("settings_deadline"), "", true);
            $item->setValue($value[self::F_DEADLINE]);
            $form->addItem($item);

            $item = new \ilNonEditableValueGUI($this->txt("settings_edit_notes"), "", true);
            $val = $this->txt("no");
            if ($value[self::F_EDIT_NOTES]) {
                $val = $this->txt("yes");
            }
            $item->setValue($val);
            $form->addItem($item);
        }
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        foreach ($data as $acco_id => $value) {
            $acco = \ilObjectFactory::getInstanceByRefId($acco_id);
            $this->request_builder->addConfigurationFor(
                $acco,
                [
                    self::F_DATE_SOURCE => $value[self::F_DATE_SOURCE],
                    self::F_STARTDATE => $value[self::F_STARTDATE],
                    self::F_ENDDATE => $value[self::F_ENDDATE],

                    self::F_VENUE_SOURCE => $value[self::F_VENUE_SOURCE],
                    self::F_VENUE => $value[self::F_VENUE],

                    self::F_PRIOR => $value[self::F_PRIOR],
                    self::F_FOLLOWING => $value[self::F_FOLLOWING],
                    self::F_DEADLINE => $value[self::F_DEADLINE],
                    self::F_EDIT_NOTES => $value[self::F_EDIT_NOTES],
                ]
            );
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $post = $_POST;
        $accomodations = $this->transformData($post);

        $ok = true;
        foreach ($accomodations as $acco_id => $values) {
            if ($values[self::F_DATE_SOURCE] == self::F_DATE_FROM_SETTINGS) {
                if ($values[self::F_STARTDATE] > $values[self::F_ENDDATE]) {
                    $item = $form->getItemByPostVar($acco_id . self::splitter . self::F_ENDDATE);
                    $item->setAlert($this->txt("end_cant_be_smaller_then_start"));
                    $ok = false;
                }
            }
        }

        if ($ok === false) {
            $this->addDataToForm($form, $accomodations);
            return null;
        }

        return $accomodations;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 500;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        $accomodations = $this->getAccomodations();
        return count($accomodations) > 0;
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Adds infos of course classification to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addAccomodationInfos(\ilPropertyFormGUI $form, array $accomodations)
    {
        foreach ($accomodations as $key => $accomodation) {
            $actions = $accomodation->getActions();

            $sec = new \ilFormSectionHeaderGUI();
            $sec->setTitle($accomodation->getTitle());
            $form->addItem($sec);

            $settings = $actions->getObjSettings();

            $date_source = self::F_DATE_FROM_SETTINGS;
            if ($settings->getDatesByCourse() === true) {
                $date_source = self::F_DATE_FROM_COURSE;
            }
            $dsource = new \ilRadioGroupInputGUI($this->txt('settings_date_source'), $accomodation->getRefId() . self::splitter . self::F_DATE_SOURCE);
            $dsource->setRequired(true);
            $dsource->setValue($date_source);
            $ro_fromcourse = new \ilRadioOption($this->txt("settings_date_source_course"), self::F_DATE_FROM_COURSE);
            $ro_fromsettings = new \ilRadioOption($this->txt("settings_date_source_settings"), self::F_DATE_FROM_SETTINGS);

            $di_start = new \ilDateTimeInputGUI($this->txt('settings_date_start'), $accomodation->getRefId() . self::splitter . self::F_STARTDATE);
            $di_start->setRequired(true);
            $di_end = new \ilDateTimeInputGUI($this->txt('settings_date_end'), $accomodation->getRefId() . self::splitter . self::F_ENDDATE);
            $di_end->setRequired(true);

            $startdate = $settings->getStartDate();
            if (!is_null($startdate)) {
                $startdate = new \ilDate($startdate->format('Y-m-d'), IL_CAL_DATE);
                $di_start->setDate($startdate);
            }
            $enddate = $settings->getEndDate();
            if (!is_null($enddate)) {
                $enddate = new \ilDate($enddate->format('Y-m-d'), IL_CAL_DATE);
                $di_end->setDate($enddate);
            }
            $ro_fromsettings->addSubItem($di_start);
            $ro_fromsettings->addSubItem($di_end);

            $dsource->addOption($ro_fromcourse);
            $dsource->addOption($ro_fromsettings);
            $form->addItem($dsource);


            $venue_source = self::F_VENUE_FROM_SELECTION;
            if ($settings->getLocationFromCourse() === true) {
                $venue_source = self::F_VENUE_FROM_COURSE;
            }
            $vsource = new \ilRadioGroupInputGUI($this->txt('settings_venue_source'), $accomodation->getRefId() . self::splitter . self::F_VENUE_SOURCE);
            $vsource->setRequired(true);
            $vsource->setValue($venue_source);
            if (\ilPluginAdmin::isPluginActive('venues') === true) {
                $ro_fromcourse = new \ilRadioOption($this->txt("settings_venue_source_course"), self::F_VENUE_FROM_COURSE);
                $ro_selection = new \ilRadioOption($this->txt("settings_venue_source_selection"), self::F_VENUE_FROM_SELECTION);

                $venue_options = $actions->getVenueListFromPlugin();
                $base = array(null => $this->txt("settings_venue_empty_option"));
                $venue_options = $base + $venue_options;

                $si = new \ilSelectInputGUI($this->txt("settings_venue"), $accomodation->getRefId() . self::splitter . self::F_VENUE);
                $si->setOptions($venue_options);
                $si->setDisabled($lock);
                $si->setRequired(true);
                $si->setValue($settings->getLocationObjId());
                $ro_selection->addSubItem($si);

                $vsource->addOption($ro_fromcourse);
                $vsource->addOption($ro_selection);
            } else {
                $vsource->setAlert($this->txt('alert_venue_plug_not_active'));
            }
            $form->addItem($vsource);


            $item = new \ilCheckboxInputGUI($this->txt("settings_prior_day"), $accomodation->getRefId() . self::splitter . self::F_PRIOR);
            $item->setValue(1);
            $item->setChecked($settings->isPriorDayAllowed());
            $form->addItem($item);

            $item = new \ilCheckboxInputGUI($this->txt("settings_following_day"), $accomodation->getRefId() . self::splitter . self::F_FOLLOWING);
            $item->setValue(1);
            $item->setChecked($settings->isFollowingDayAllowed());
            $form->addItem($item);

            $item = new \ilNumberInputGUI($this->txt("settings_deadline"), $accomodation->getRefId() . self::splitter . self::F_DEADLINE);
            $item->setValue($settings->getBookingEnd());
            $form->addItem($item);

            $item = new \ilCheckboxInputGUI($this->txt("settings_edit_notes"), $accomodation->getRefId() . self::splitter . self::F_EDIT_NOTES);
            $item->setValue(1);
            $item->setChecked($settings->getEditNotes());
            $form->addItem($item);
        }
    }

    protected function getAccomodations() : array
    {
        $xoacs = $this->getAllChildrenOfByType($this->getEntityRefId(), "xoac");
        $xoacs = array_filter($xoacs, function ($xccl) {
            $xoac_ref_id = $xccl->getRefId();
            return $this->checkAccess(["visible", "read", "copy"], $xoac_ref_id);
        });

        return $xoacs;
    }

    /**
     * Split data array in usefull array
     *
     * @param string[] $data
     *
     * @return string[]
     */
    protected function transformData(array $data)
    {
        $ret = array();
        foreach ($data as $key => $value) {
            if ($key == "cmd" || $key == "next") {
                continue;
            }
            $keys = explode(self::splitter, $key);
            $ret[(int) $keys[0]][$keys[1]] = $value;
        }

        return $ret;
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt(string $id)
    {
        return call_user_func($this->txt, $id);
    }
}
