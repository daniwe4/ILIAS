<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to configure the course periods
 */
class CoursePeriodStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_COURSE_PERIOD = "course_period";
    const F_START = "start";
    const F_END = "end";

    const F_SESSION = "session";

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

    public function __construct(Entity $entity, \Closure $txt, $owner)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->owner = $owner;
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
        return $this->txt("training_period");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("training_period_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");
        include_once("Services/jQuery/classes/class.iljQueryUtil.php");

        $tpl = $this->getDIC()->ui()->mainTemplate();
        $tpl->addJavaScript(
            $this->owner->getPluginDirectory() . "/templates/readonly_enddate.js"
        );

        \iljQueryUtil::initjQuery();

        $this->addCourseInfos($form);
        $this->addSessionInfos($form);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $form->setValuesByArray($data);

        $pstart = self::F_SESSION . "_" . self::F_START;
        if (array_key_exists($pstart, $data)) {
            foreach ($data[$pstart] as $ref_id => $data) {
                $session = \ilObjectFactory::getInstanceByRefId($ref_id);
                $appointment = $session->getFirstAppointment();
                $start_hour = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H");
                $start_minutes = $appointment->getStart()->get(IL_CAL_FKT_DATE, "i");

                $hh = (int) $data["hh"];
                $mm = (int) $data["mm"];

                $item = $form->getItemByPostVar($pstart . "[" . $ref_id . "]");
                $item->setHours($hh);
                $item->setMinutes($mm);

                if (is_null($this->getAgendaOfSession($session))) {
                    $end_hour = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H");
                    $end_minutes = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "i");
                    $pend = self::F_SESSION . "_" . self::F_END;

                    $item = $form->getItemByPostVar($pend . "[" . $ref_id . "]");
                    $item->setHours($hh + $end_hour - $start_hour);
                    $item->setMinutes($mm + $end_minutes - $start_minutes);
                }
            }
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $lng = $this->getIlLanguage();
        $lng->loadLanguageModule("crs");
        $lng->loadLanguageModule("sess");

        $course_period = $data[self::F_COURSE_PERIOD];
        $item = new \ilNonEditableValueGUI($lng->txt("crs_period"), "", true);
        $output = $course_period["start"]
            . " - "
            . $course_period["end"]
        ;

        $item->setValue($output);
        $form->addItem($item);

        $has_agenda = $this->hasAgendaAsChild();
        $pstart = self::F_SESSION . "_" . self::F_START;

        if (array_key_exists($pstart, $data)) {
            foreach ($data[$pstart] as $ref_id => $data) {
                $session = \ilObjectFactory::getInstanceByRefId($ref_id);
                $appointment = $session->getFirstAppointment();
                $offset = $appointment->getDaysOffset();

                if (is_null($offset)) {
                    $title = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
                } else {
                    $title = $this->txt("day") . " " . $offset;
                }

                $hh = $data["hh"];
                $mm = $data["mm"];

                $hh = str_pad($hh, 2, "0", STR_PAD_LEFT);
                $mm = str_pad($mm, 2, "0", STR_PAD_LEFT);

                $timing = "$hh:$mm - ";

                if ($has_agenda) {
                    $timing .= $this->txt("set_by_agenda");
                } else {
                    $start_hour = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H");
                    $end_hour = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H");
                    $start_minutes = $appointment->getStart()->get(IL_CAL_FKT_DATE, "i");
                    $end_minutes = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "i");
                    $hh = (int) $hh + $end_hour - $start_hour;
                    $mm = (int) $mm + $end_minutes - $start_minutes;

                    if ($mm < 0) {
                        $hh = $hh - 1;
                        $mm = 60 + $mm;
                    }

                    if ($mm >= 60) {
                        $hh = $hh + 1;
                        $mm = $mm - 60;
                    }

                    $hh = str_pad($hh, 2, "0", STR_PAD_LEFT);
                    $mm = str_pad($mm, 2, "0", STR_PAD_LEFT);

                    $timing = "$timing $hh:$mm";
                }

                $item = new \ilNonEditableValueGUI($title, "", true);
                $item->setValue($timing);
                $form->addItem($item);
            }
        } else {
            if ($this->hasAgendaAsChild()) {
                foreach ($this->getSessions() as $session) {
                    $ref_id = $session->getRefId();
                    $appointment = $session->getFirstAppointment();
                    $offset = $appointment->getDaysOffset();

                    if (is_null($offset)) {
                        $title = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
                    } else {
                        $title = $this->txt("day") . " " . $offset;
                    }

                    $item = new \ilNonEditableValueGUI($title, "", true);
                    $item->setValue($this->txt("set_by_agenda"));
                    $form->addItem($item);
                }
            }
        }
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $crs = $this->getEntityObject();
        $this->request_builder->addConfigurationFor(
            $crs,
            ["course_period" => $data["course_period"]]
        );

        $key = self::F_SESSION . "_" . self::F_START;
        if (array_key_exists($key, $data)) {
            foreach ($data[$key] as $ref_id => $d) {
                $sess = \ilObjectFactory::getInstanceByRefId($ref_id);
                $this->request_builder->addConfigurationFor(
                    $sess,
                    ["session_time" => $d]
                );
            }
        }

        if ($this->hasAgendaAsChild()) {
            foreach ($this->getSessions() as $session) {
                $this->request_builder->addConfigurationFor(
                    $session,
                    ["update_from_agenda" => true]
                );

                $agenda_ref_id = $this->getAgendaOfSession($session);
                if (!is_null($agenda_ref_id)) {
                    $agenda = \ilObjectFactory::getInstanceByRefId($agenda_ref_id);
                    $this->request_builder->addConfigurationFor(
                        $agenda,
                        [
                            "start_time" => $data[$agenda_ref_id]["start_time"]
                        ]
                    );
                }
            }
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = array();
        $data[self::F_COURSE_PERIOD] = $form->getInput(self::F_COURSE_PERIOD);

        $crs = $this->getEntityObject();
        $settings = $this->owner->getExtendedSettings();
        if ($crs->getCourseStart() && $settings->getTimeMode() == "only_future") {
            $crs_start = $crs->getCourseStart()->getUnixTime();
            $crs_end = $crs->getCourseEnd()->getUnixTime();
            $diff = $crs_end - $crs_start;

            $start = new \ilDateTime(date("Y-m-d"), IL_CAL_DATE);
            $end = new \ilDateTime($start->getUnixTime() + $diff, IL_CAL_UNIX);

            $start->increment(\ilDate::DAY, $settings->getMinDaysInFuture());
            $user_start = \DateTime::createFromFormat("d.m.Y", $data[self::F_COURSE_PERIOD]["start"]);

            if ($start->get(IL_CAL_UNIX) > $user_start->getTimestamp()) {
                \ilUtil::sendFailure(sprintf($this->txt("start_must_be_in_future"), $start->get(IL_CAL_FKT_DATE, "d.m.Y")));
                return null;
            }
        }

        $pvar = self::F_SESSION . "_" . self::F_START;
        $post = $_POST;
        if (array_key_exists($pvar, $post)) {
            $data[$pvar] = [];
            foreach ($_POST[$pvar] as $ref_id => $values) {
                $sess = \ilObjectFactory::getInstanceByRefId($ref_id);
                $agenda_ref_id = $this->getAgendaOfSession($sess);
                if (substr($values["hh"], 0, 1) == "0") {
                    $values["hh"] = substr($values["hh"], 1, 1);
                }
                if (substr($values["mm"], 0, 1) == "0") {
                    $values["mm"] = substr($values["mm"], 1, 1);
                }
                if (!is_null($agenda_ref_id)) {
                    $data[$agenda_ref_id]["start_time"] = (int) $values["hh"] * 60 + (int) $values["mm"];
                }

                $data[$pvar][$ref_id]["hh"] = (int) $values["hh"];
                $data[$pvar][$ref_id]["mm"] = (int) $values["mm"];
            }
        }

        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 200;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return true;
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
     * Get the  entity object
     *
     * @return int
     */
    protected function getEntityObject()
    {
        return $this->entity()->object();
    }

    /**
     * Adds infos of course classification to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addCourseInfos(\ilPropertyFormGUI $form)
    {
        $crs = $this->getEntityObject();
        $lng = $this->getIlLanguage();
        $lng->loadLanguageModule("crs");
        $lng->loadLanguageModule("sess");

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("sec_schedule"));
        $form->addItem($sec);

        require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
        $ddi = new \ilDateDurationInputGUI($lng->txt("crs_period"), self::F_COURSE_PERIOD);
        $ddi->setRequired(true);

        if ($crs->getCourseStart()) {
            $crs_start = $crs->getCourseStart()->getUnixTime();
            $crs_end = $crs->getCourseEnd()->getUnixTime();
            $diff = $crs_end - $crs_start;

            $start = new \ilDateTime(date("Y-m-d"), IL_CAL_DATE);
            $end = new \ilDateTime($start->getUnixTime() + $diff, IL_CAL_UNIX);

            $settings = $this->owner->getExtendedSettings();
            if ($settings->getTimeMode() == "only_future") {
                $start->increment(\ilDate::DAY, $settings->getMinDaysInFuture());
                $end->increment(\ilDate::DAY, $settings->getMinDaysInFuture());
            }

            $ddi->setStart($start);
            $ddi->setEnd($end);
        }

        $form->addItem($ddi);
    }

    /**
     * Adds infos of all sessions form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addSessionInfos(\ilPropertyFormGUI $form)
    {
        $lng = $this->getIlLanguage();

        $has_agenda = $this->hasAgendaAsChild();
        foreach ($this->getSessions() as $session) {
            $ref_id = $session->getRefId();
            $appointment = $session->getFirstAppointment();
            $offset = $appointment->getDaysOffset();

            if (is_null($offset)) {
                $sec_title = $appointment->getStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
            } else {
                $sec_title = $this->txt("day") . " " . $offset;
            }

            $sec = new \ilFormSectionHeaderGUI();
            $sec->setTitle($sec_title);
            $form->addItem($sec);

            if (!$has_agenda) {
                $start_hour = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H");
                $end_hour = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H");
                $start_minutes = $appointment->getStart()->get(IL_CAL_FKT_DATE, "i");
                $end_minutes = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "i");

                $sess_dur = new \ilSessionDurationInputGUI(
                    $lng->txt('event_start_time'),
                    self::F_SESSION . "_" . self::F_START . "[{$ref_id}]"
                );

                $sess_dur->setMinuteStepSize(5);
                $sess_dur->setShowMonths(false);
                $sess_dur->setShowDays(false);
                $sess_dur->setHours($start_hour);
                $sess_dur->setMinutes($start_minutes);
                $form->addItem($sess_dur);

                $sess_dur = new \ilSessionDurationInputGUI(
                    $lng->txt('event_end_time'),
                    self::F_SESSION . "_" . self::F_END . "[{$ref_id}]"
                );

                // TODO: this needs to be fixed, ilSessionDurationInputGUI has no such
                // method.
                $sess_dur->setReadOnly(true);
                $sess_dur->setMinuteStepSize(5);
                $sess_dur->setShowMonths(false);
                $sess_dur->setShowDays(false);
                $sess_dur->setHours($end_hour);
                $sess_dur->setMinutes($end_minutes);
                $form->addItem($sess_dur);
            } else {
                $start_hour = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H");
                $start_minutes = $appointment->getStart()->get(IL_CAL_FKT_DATE, "i");

                $sess_dur = new \ilSessionDurationInputGUI(
                    $lng->txt('event_start_time'),
                    self::F_SESSION . "_" . self::F_START . "[{$ref_id}]"
                );

                $sess_dur->setMinuteStepSize(5);
                $sess_dur->setShowMonths(false);
                $sess_dur->setShowDays(false);
                $sess_dur->setHours($start_hour);
                $sess_dur->setMinutes($start_minutes);
                $form->addItem($sess_dur);

                $ne = new \ilNonEditableValueGUI($lng->txt("event_end_time"), "", false);
                $ne->setValue($this->txt("set_by_agenda"));
                $form->addItem($ne);
            }
        }
    }

    /**
     * Get CourseClassification where user as permission to copy
     *
     * @return ilObjCourseClassification | null
     */
    protected function getSessions()
    {
        $sesss = $this->getAllChildrenOfByType($this->getEntityRefId(), "sess");
        $sesss = array_filter($sesss, function ($sess) {
            $sess_ref_id = $sess->getRefId();
            return $this->checkAccess(["visible", "read", "copy"], $sess_ref_id);
        });

        $actions = $this->owner->getActions();
        $sesss = array_filter($sesss, function ($sess) use ($actions) {
            $sess_ref_id = $sess->getRefId();
            $copy_settings = $actions->getCopySettingsByRefId((int) $sess_ref_id);
            return $copy_settings && $copy_settings->getProcessType() == \CaT\Plugins\CopySettings\Children\Child::COPY;
        });

        usort($sesss, function ($a, $b) {
            $appointment = $a->getFirstAppointment();
            $start_a = $appointment->getStart()->get(IL_CAL_DATETIME);

            $appointment = $b->getFirstAppointment();
            $start_b = $appointment->getStart()->get(IL_CAL_DATETIME);

            if ($start_a > $start_b) {
                return 1;
            }

            if ($start_b > $start_a) {
                return -1;
            }

            return 0;
        });

        if (count($sesss) == 0) {
            return array();
        }

        return $sesss;
    }

    /**
     * Get day times as string
     *
     * @param string[]
     *
     * @return string
     */
    protected function getTimesAsString(array $times)
    {
        return str_pad(
            $times["hh"],
            2,
            "0",
            STR_PAD_LEFT
        )
            . ":"
            . str_pad($times["mm"], 2, "0", STR_PAD_LEFT);
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
     * Get the ilLanguage
     *
     * @return \ilLanguage
     */
    protected function getIlLanguage()
    {
        return $this->getDIC()->language();
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

    /**
     * Get EduTracking where user as permission to copy
     *
     * @return ilObjEduTracking | null
     */
    protected function getAgenda()
    {
        $xages = $this->getAllChildrenOfByType($this->getEntityRefId(), "xage");

        if (count($xages) == 0) {
            return null;
        }

        return array_shift($xages);
    }

    protected function hasAgendaAsChild()
    {
        return is_null($this->getAgenda()) === false;
    }

    /**
     * @return int|null
     */
    protected function getAgendaOfSession(\ilObjSession $session)
    {
        $event_items = (new \ilEventItems($session->getId()))->getItems();
        foreach ($event_items as $event_item) {
            if (\ilObject::_lookupType($event_item, true) == "xage") {
                return (int) $event_item;
            }
        }

        return null;
    }
}
