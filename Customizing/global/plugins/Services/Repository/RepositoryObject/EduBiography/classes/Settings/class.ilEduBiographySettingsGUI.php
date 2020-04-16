<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Settings\SettingsRepository;

class ilEduBiographySettingsGUI
{
    const CMD_VIEW = 'view_settings';
    const CMD_SAVE = 'save_settings';

    const POST_TITLE = 'title';
    const POST_DESCRIPTION = 'description';
    const POST_ONLINE = 'online';
    const POST_HAS_SUPERIOR_OVERVIEW = 'has_superior_overview';
    const POST_INIT_VISIBLE_COLUMNS = 'init_visible_columns';
    const POST_INVISIBLE_COURSE_TOPICS = "invisible_course_topics";
    const POST_RECOMMENDATION_ALLOWED = "recommendation_ok";

    /**
     * @var ilObjEduBiography
     */
    protected $object;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var SettingsRepository
     */
    protected $db;

    public function __construct(
        ilObjEduBiography $object,
        ilPlugin $plugin,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilLanguage $lng,
        ilAccess $access,
        Closure $txt,
        SettingsRepository $db
    ) {
        $this->object = $object;
        $this->plugin = $plugin;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->access = $access;
        $this->txt = $txt;
        $this->db = $db;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SAVE:
                if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->saveSettings();
                }
                break;
            case self::CMD_VIEW:
                if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
                    $this->renderSettings();
                }
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function renderSettings()
    {
        $settings = $this->object->settings();
        $form = $this->buildForm();
        $form->getItemByPostVar(self::POST_TITLE)->setValue($this->object->getTitle());
        $form->getItemByPostVar(self::POST_DESCRIPTION)->setValue($this->object->getDescription());
        if ($settings->isOnline()) {
            $form->getItemByPostVar(self::POST_ONLINE)->setChecked(true);
        }
        if ($settings->hasSuperiorOverview()) {
            $form->getItemByPostVar(self::POST_HAS_SUPERIOR_OVERVIEW)->setChecked(true);
            $form->getItemByPostVar(self::POST_INIT_VISIBLE_COLUMNS)->setValue($settings->getInitVisibleColumns());
            $form->getItemByPostVar(self::POST_INVISIBLE_COURSE_TOPICS)->setValue($settings->getInvisibleCourseTopics());
        }
        if ($settings->getRecommendationAllowed()) {
            $form->getItemByPostVar(self::POST_RECOMMENDATION_ALLOWED)->setChecked(true);
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

    public function saveSettingsData(ilPropertyFormGUI $form)
    {
        $this->object->setTitle($form->getItemByPostVar(self::POST_TITLE)->getValue());
        $this->object->setDescription($form->getItemByPostVar(self::POST_DESCRIPTION)->getValue());
        $this->object->setSettings(
            $this->object->settings()
                ->withIsOnline((bool) $form->getItemByPostVar(self::POST_ONLINE)->getChecked())
                ->withHasSuperiorOverview((bool) $form->getItemByPostVar(self::POST_HAS_SUPERIOR_OVERVIEW)->getChecked())
                ->withInitVisibleColumns($form->getItemByPostVar(self::POST_INIT_VISIBLE_COLUMNS)->getValue())
                ->withInvisibleCourseTopics($form->getItemByPostVar(self::POST_INVISIBLE_COURSE_TOPICS)->getValue())
                ->withRecommendationAllowed((bool) $form->getItemByPostVar(self::POST_RECOMMENDATION_ALLOWED)->getChecked())
        );
        $this->object->update();
    }

    protected function buildForm() : ilPropertyFormGUI
    {
        $settings_form = new ilPropertyFormGUI();
        $settings_form->setTitle($this->txt("settings"));
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

        $cb = new ilCheckboxInputGUI(
            $this->txt("recommendation_allowed"),
            self::POST_RECOMMENDATION_ALLOWED
        );
        $cb->setValue(1);
        $settings_form->addItem($cb);

        $has_superior_overview = new ilCheckboxInputGUI($this->txt('has_superior_overview'), self::POST_HAS_SUPERIOR_OVERVIEW);
        $has_superior_overview->setValue(1);
        $mi = new ilMultiSelectInputGUI($this->txt('visible_columns'), self::POST_INIT_VISIBLE_COLUMNS);
        $mi->setInfo($this->txt("visible_columns_info"));
        $mi->setWidth(100);
        $mi->setWidthUnit("%");
        $mi->setHeight(200);
        $options = $this->plugin->getAllVisibleFields();

        uasort($options, function ($a, $b) {
            return strcasecmp($a, $b);
        });
        $mi->setOptions($options);
        $has_superior_overview->addSubItem($mi);

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
        $has_superior_overview->addSubItem($mi);

        $settings_form->addItem($has_superior_overview);

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
