<?php

use \CaT\Plugins\OnlineSeminar;

/**
 * Class for edit settings of online seminar
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilOnlineSeminarGUI
{
    use OnlineSeminar\Settings\ilFormHelper;

    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_VC = "f_vc";
    const F_SCHEDULE = "f_schedule";
    const F_ADMISSION = "f_admission";
    const F_URL = "f_url";
    const F_ONLINE = "f_online";

    /**
     * @var OnlineSeminar\Settings\ilActions
     */
    protected $actions;

    /**
     * @var OnlineSeminar\VC\VCActions
     */
    protected $vc_actions;

    /**
     * @var ilCrtl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    public function __construct(
        OnlineSeminar\ilActions $actions,
        OnlineSeminar\VC\VCActions $vc_actions,
        OnlineSeminar\VC\FormHelper $vc_form_helper
    ) {
        $this->actions = $actions;
        $this->vc_actions = $vc_actions;
        $this->vc_form_helper = $vc_form_helper;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_EDIT_PROPERTIES);

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show form to edit properties
     *
     * @param $form | null 	$form
     *
     * @return null
     */
    protected function editProperties(\ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save new propties
     *
     * @return null
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editProperties($form);
            return;
        }

        $post = $_POST;
        $object = $this->actions->getObject();
        $object->setTitle($post[self::F_TITLE]);
        $object->setDescription($post[self::F_DESCRIPTION]);

        $fnc = function ($s) use ($post) {
            require_once("Services/Calendar/classes/class.ilDateTime.php");
            $schedule = $post[self::F_SCHEDULE];

            require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
            $beginning = ilCalendarUtil::parseIncomingDate($schedule["start"], IL_CAL_DATETIME);
            $ending = ilCalendarUtil::parseIncomingDate($schedule["end"], IL_CAL_DATETIME);

            return $s->withBeginning($beginning)
                ->withEnding($ending)
                ->withAdmission($post[self::F_ADMISSION])
                ->withUrl($post[self::F_URL])
                ->withOnline((bool) $post[self::F_ONLINE]);
        };

        $this->vc_form_helper->saveRequiredValues($post);

        $object->updateSettings($fnc);
        $object->update();

        ilUtil::sendSuccess($this->txt("properties_saved"), true);
        $this->g_ctrl->redirect($this);
    }

    /**
     * Init property form
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings"));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $this->addPropertyItems($form);
        $this->vc_form_helper->addRequiredFormItems($form);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    /**
     * Fill form with current values
     *
     * @param ilPropertyFormGUI 	$form
     *
     * @return null
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();
        $object = $this->actions->getObject();
        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();

        $settings = $object->getSettings();
        $values[self::F_VC] = $this->txt(strtolower($settings->getVCType()));

        $beginning = $settings->getBeginning();
        if ($beginning !== null) {
            $values[self::F_SCHEDULE]['start'] = $beginning;
        }

        $ending = $settings->getEnding();
        if ($ending !== null) {
            $values[self::F_SCHEDULE]['end'] = $ending;
        }

        $values[self::F_ADMISSION] = $settings->getAdmission();
        $values[self::F_URL] = $settings->getUrl();
        $values[self::F_ONLINE] = $settings->getOnline();

        $this->vc_form_helper->getFormValues($values);

        $form->setValuesByArray($values);
    }

    /**
     * Translate code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->actions->getObject()->pluginTxt($code);
    }
}
