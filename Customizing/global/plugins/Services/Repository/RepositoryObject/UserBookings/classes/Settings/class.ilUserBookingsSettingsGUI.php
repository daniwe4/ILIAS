<?php

use CaT\Plugins\UserBookings\ilObjActions;
use CaT\Plugins\UserBookings\Settings\UserBookingsSettings;

class ilUserBookingsSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_SUPERIOR_VIEW = "f_superior_view";
    const F_LOCAL_EVALUATION = "f_local_evaluation";
    const F_RECOMMENDATION_ALLOWED = "f_recommendation_ok";

    /**
     * @var ilObjUserBookingsGUI
     */
    protected $parent;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilObjActions
     */
    protected $actions;

    public function __construct(ilObjUserBookingsGUI $parent, ilObjActions $actions)
    {
        global $DIC;

        $this->parent = $parent;
        $this->actions = $actions;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->save();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Show the properties form gui
     *
     * @param ilPropertyFormGUI | null 	$form
     *
     * @return void
     */
    protected function editProperties($form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Saves the properties
     *
     * @return void
     */
    protected function save()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $post = $_POST;
        $title = trim($post[self::F_TITLE]);
        $description = trim($post[self::F_DESCRIPTION]);
        $superior_view = false;
        if (isset($post[self::F_SUPERIOR_VIEW]) && (int) $post[self::F_SUPERIOR_VIEW] == 1) {
            $superior_view = true;
        }
        $local_evaluation = false;
        if ($superior_view &&
            isset($post[self::F_LOCAL_EVALUATION]) &&
            (int) $post[self::F_LOCAL_EVALUATION] == 1
        ) {
            $local_evaluation = true;
        }

        $recommendation_allowed = false;
        if (isset($post[self::F_RECOMMENDATION_ALLOWED]) &&
            (int) $post[self::F_RECOMMENDATION_ALLOWED] == 1
        ) {
            $recommendation_allowed = true;
        }

        $object = $this->actions->getObject();
        $object->setTitle($title);
        $object->setDescription($description);

        $fnc = function (UserBookingsSettings $s) use ($superior_view, $local_evaluation, $recommendation_allowed) {
            return $s->withSuperiorView($superior_view)
                    ->withLocalEvaluation($local_evaluation)
                    ->withRecommendationAllowed($recommendation_allowed);
        };

        $object->updateSettings($fnc);
        $object->update();

        ilUtil::sendSuccess($this->txt("successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Inits the property form
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("xubk_settings"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $cb = new ilCheckboxInputGUI($this->txt("superior_view"), self::F_SUPERIOR_VIEW);
        $cb->setValue(1);
        $cb->setInfo($this->txt("superior_view_info"));
        $sub_cb = new ilCheckboxInputGUI($this->txt("local_evaluation"), self::F_LOCAL_EVALUATION);
        $sub_cb->setValue(1);
        $sub_cb->setInfo($this->txt("local_evaluation_info"));
        $cb->addSubItem($sub_cb);
        $form->addItem($cb);

        $cb = new ilCheckboxInputGUI(
            $this->txt("recommendation_allowed"),
            self::F_RECOMMENDATION_ALLOWED
        );
        $cb->setValue(1);
        $form->addItem($cb);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    /**
     * Fill form with values
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $object = $this->actions->getObject();
        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();
        $values[self::F_SUPERIOR_VIEW] = $object->getSettings()->getSuperiorView();
        $values[self::F_LOCAL_EVALUATION] = $object->getSettings()->getLocalEvaluation();
        $values[self::F_RECOMMENDATION_ALLOWED] = $object->getSettings()->getRecommendationAllowed();
        $form->setValuesByArray($values);
    }

    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}
