<?php

declare(strict_types=1);

use CaT\Plugins\EmployeeBookingOverview\Settings\SettingsRepository;

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

class ilEmployeeBookingOverviewSettingsGUI
{
    const CMD_VIEW = 'view_settings';
    const CMD_SAVE = 'save_settings';

    const POST_TITLE = 'title';
    const POST_DESCRIPTION = 'description';
    const POST_ONLINE = 'online';
    const POST_GLOBAL = 'global';
    const POST_INVISIBLE_COURSE_TOPICS = "invisible_course_topics";

    protected $object;
    protected $ctrl;
    protected $access_checker;
    protected $tpl;
    protected $db;

    public function __construct(
        ilObjEmployeeBookingOverview $object,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        AccessChecker $access_checker,
        Closure $txt,
        SettingsRepository $db
    ) {
        $this->object = $object;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->access_checker = $access_checker;
        $this->txt = $txt;
        $this->db = $db;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SAVE:
                if ($this->access_checker->canWrite()) {
                    return $this->saveSettings();
                }
                break;
            case self::CMD_VIEW:
                if ($this->access_checker->canWrite()) {
                    return $this->renderSettings();
                }
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function renderSettings()
    {
        $settings = $this->object->getSettings();
        $form = $this->buildForm();
        $form->getItemByPostVar(self::POST_TITLE)
            ->setValue($this->object->getTitle());
        $form->getItemByPostVar(self::POST_DESCRIPTION)
            ->setValue($this->object->getDescription());
        $form->getItemByPostVar(self::POST_INVISIBLE_COURSE_TOPICS)
            ->setValue($settings->getInvisibleCourseTopics());

        if ($settings->isOnline()) {
            $form->getItemByPostVar(self::POST_ONLINE)->setChecked(true);
        }
        if ($settings->isGlobal()) {
            $form->getItemByPostVar(self::POST_GLOBAL)->setChecked(true);
        }

        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $form = $this->buildForm();
        $form->setValuesByPost();
        if ($form->checkInput()) {
            $this->saveSettingsData($form);
            $red = $this->ctrl->getLinkTarget($this, self::CMD_VIEW, '', false, false);
            ilUtil::sendSuccess($this->txt('settings_saved_confirm'), true);
            ilUtil::redirect($red);
        }
        $this->tpl->setContent($form->getHtml());
    }

    public function saveSettingsData($form)
    {
        $this->object->setTitle($form->getItemByPostVar(self::POST_TITLE)->getValue());
        $this->object->setDescription($form->getItemByPostVar(self::POST_DESCRIPTION)->getValue());
        $this->object->setSettings(
            $this->object->getSettings()
                ->withOnline((bool) $form->getItemByPostVar(self::POST_ONLINE)->getChecked())
                ->withGlobal((bool) $form->getItemByPostVar(self::POST_GLOBAL)->getChecked())
                ->withInvisibleCourseTopics($form->getItemByPostVar(self::POST_INVISIBLE_COURSE_TOPICS)->getValue())
        );
        $this->object->update();
    }

    protected function buildForm()
    {
        $settings_form = new ilPropertyFormGUI();
        $settings_form->setFormAction($this->ctrl->getFormAction($this));
        $settings_form->addCommandButton(self::CMD_SAVE, $this->txt("save"));

        $title = new ilTextInputGUI($this->txt('title'), self::POST_TITLE);
        $title->setRequired(true);
        $settings_form->addItem($title);

        $description = new ilTextAreaInputGUI($this->txt('description'), self::POST_DESCRIPTION);
        $settings_form->addItem($description);

        $online = new ilCheckboxInputGUI($this->txt('online'), self::POST_ONLINE);
        $online->setValue(1);
        $settings_form->addItem($online);

        $global = new ilCheckboxInputGUI($this->txt('global'), self::POST_GLOBAL);
        $global->setValue(1);
        $settings_form->addItem($global);

        $mi = new ilMultiSelectInputGUI($this->txt('invisible_course_topics'), self::POST_INVISIBLE_COURSE_TOPICS);
        $mi->setInfo($this->txt("invisible_course_topics_info"));
        $mi->setWidth(100);
        $mi->setWidthUnit("%");
        $mi->setHeight(200);
        $options = $this->getCourseTopics();

        uasort($options, function ($a, $b) {
            return strcasecmp($a, $b);
        });
        $mi->setOptions($options);
        $settings_form->addItem($mi);

        return $settings_form;
    }

    protected function getCourseTopics() : array
    {
        $xccl_course_topics = [];

        if (ilPluginAdmin::isPluginActive("xccl")) {
            $pl = ilPluginAdmin::getPluginObjectById("xccl");
            foreach ($pl->getCourseTopics() as $course_topic) {
                $caption = $course_topic->getCaption();
                $xccl_course_topics[$caption] = $caption;
            }
        }
        foreach ($this->db->getHistoricCourseTopics() as $topic) {
            if (!key_exists($topic, $xccl_course_topics)) {
                $xccl_course_topics[$topic] = $topic;
            }
        }
        return array_unique($xccl_course_topics);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
