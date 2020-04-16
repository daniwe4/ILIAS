<?php

declare(strict_types=1);

use CaT\Plugins\EduTracking\Purposes\GTI;

/**
 * Repository configuration for GTI purpose
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilGTIGUI
{
    const CMD_EDIT = "editGTI";
    const CMD_SAVE = "saveGTI";

    const F_CATEGORIES = "categories";
    const F_SET_TRAININGTIMES_MANUALLY = "set_trainingtimes_manually";
    const F_TIME = "time";

    /**
     * @var ilEduTrackingSettingsGUI
     */
    protected $parent;

    /**
     * @var GTI\ilObjActions
     */
    protected $actions;

    /**
     * @var GTI\Configuration\ilActions
     */
    protected $config_actions;

    /**
     * @var ilObjCourse
     */
    protected $parent_course;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @param bool 	$below_course
     */
    public function __construct(
        ilEduTrackingSettingsGUI $parent,
        GTI\ilActions $actions,
        GTI\Configuration\ilActions $config_actions,
        ilObjCourse $parent_course = null
    ) {
        $this->parent = $parent;
        $this->actions = $actions;
        $this->parent_course = $parent_course;
        $this->config_actions = $config_actions;
        $this->below_course = $parent_course !== null;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_EDIT:
                $this->edit();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Edit current settings
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return void
     */
    protected function edit(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT, $this->txt("cancel"));

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save current settings
     *
     * @return void
     */
    protected function save()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->edit($form);
            return;
        }

        $post = $_POST;

        $set_trainingtime_manually = (int) $post[self::F_SET_TRAININGTIMES_MANUALLY];

        $learning_time = $post[self::F_TIME];
        $minutes = $learning_time["mm"] + $learning_time["hh"] * 60;

        if (!$set_trainingtime_manually) {
            $minutes = $this->config_actions->getPlugin()->getCourseTrainingtimeInMinutes(
                (int) $this->parent_course->getRefId()
            );
        }

        $cat_id = $this->getIdOrNull($post[self::F_CATEGORIES]);

        $settings = $this->actions->select();
        $settings = $settings
            ->withCategoryId($cat_id)
            ->withSetTrainingtimeManually((bool) $post[self::F_SET_TRAININGTIMES_MANUALLY])
            ->withMinutes($minutes)
        ;
        $settings->update();

        ilUtil::sendSuccess($this->txt("gti_settings_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function getIdOrNull(string $cat_id = null)
    {
        if (!is_null($cat_id) && $cat_id != "") {
            $cat_id = (int) $cat_id;
        } else {
            $cat_id = null;
        }
        return $cat_id;
    }

    /**
     * Init the form if object is below course
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        $form = $this->getForm();

        $options = array(null => $this->txt("please_select"), 0 => "-");

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("categories"));
        $form->addItem($sh);

        $sb = new ilSelectInputGUI($this->txt("category"), self::F_CATEGORIES);
        $sb->setOptions($options + $this->getCategoriesOptions());
        $sb->setRequired(true);
        $form->addItem($sb);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("training_times"));
        $form->addItem($sh);

        $sb = new ilSelectInputGUI(
            $this->txt("manually_set_training_time"),
            self::F_SET_TRAININGTIMES_MANUALLY
        );
        $sb->setOptions(array(1 => $this->txt("yes"), 0 => $this->txt("no")));
        $form->addItem($sb);

        $ni = new \ilTimeInputGUI($this->txt("training_time"), self::F_TIME);
        $ni->setMaxHours(250);
        $ni->setMinuteStepSize(5);
        $form->addItem($ni);

        return $form;
    }

    protected function getCategoriesOptions() : array
    {
        $categories_objects = $this->config_actions->selectCategories();
        $categories = array();
        foreach ($categories_objects as $cat) {
            $categories[$cat->getId()] = $cat->getName();
        }
        $categories = $this->sortData($categories);

        return $categories;
    }

    private function sortData(array $data) : array
    {
        uasort($data, function (string $a, string $b) {
            return strcasecmp($a, $b);
        });
        return $data;
    }

    /**
     * Fills form with values
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $settings = $this->actions->select();
        $minutes = $settings->getMinutes();

        $values = array();
        if (!is_null($minutes) && $minutes > 0) {
            list($hours, $minutes) = $this->getMinuteFormValuesFromMinutes($minutes);
            $values[self::F_TIME]["hh"] = $hours;
            $values[self::F_TIME]["mm"] = $minutes;
        }

        $values[self::F_CATEGORIES] = $settings->getCategoryId();
        $values[self::F_SET_TRAININGTIMES_MANUALLY] = (int) $settings->getSetTrainingTimeManually();


        $form->setValuesByArray($values);
    }

    protected function getMinuteFormValuesFromMinutes(int $minutes) : array
    {
        $ret = [];
        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;
        $ret[] = $hours;
        $ret[] = $minutes;
        return $ret;
    }

    /**
     * Get form with basic configuration
     *
     * @return ilPropertyFormGUI
     */
    protected function getForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("gti_settings"));

        return $form;
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}
