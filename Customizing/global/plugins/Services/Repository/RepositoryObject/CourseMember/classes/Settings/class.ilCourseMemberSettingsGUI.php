<?php

use CaT\Plugins\CourseMember;

/**
 * GUI Class to edit settings of repo object
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilCourseMemberSettingsGUI
{
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_SAVE_PROPERTIES = "saveProperties";

    const F_TITLE = "f_title";
    const F_DESCRIPTION = "f_description";
    const F_CREDITS = "f_credits";
    const F_LIST_REQUIRED = "f_list_required";
    const F_LIST_OPT_TEXT = "f_list_opt_text";
    const F_LIST_OPT_ORGU = "f_list_opt_orgu";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilObjCourseMemberGUI
     */
    protected $parent;

    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct(ilObjCourseMemberGUI $parent, CourseMember\ilObjActions $actions)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->parent = $parent;
        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_EDIT_PROPERTIES:
                $this->editProperties();
                break;
            case self::CMD_SAVE_PROPERTIES:
                $this->saveProperties();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Shows the form to edit the properties
     *
     * @return void
     */
    protected function editProperties()
    {
        $form = $this->initForm();
        $this->fillForm($form);
        $this->showForm($form);
    }

    /**
     * Shows the property form
     *
     * @param ilPropertyFormGUI
     *
     * @return void
     */
    protected function showForm(ilPropertyFormGUI $form)
    {
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * saveProperties
     *
     * @return void
     */
    protected function saveProperties()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showForm($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $list_required = (bool) $post[self::F_LIST_REQUIRED];
        $list_opt_orgu = (bool) $post[self::F_LIST_OPT_ORGU];
        $list_opt_text = (bool) $post[self::F_LIST_OPT_TEXT];

        $object = $this->actions->getObject();
        $object->setTitle($title);
        $object->setDescription($description);

        $fnc = function ($s) use ($list_required, $list_opt_orgu, $list_opt_text) {
            return $s
                ->withListRequired($list_required)
                ->withListOptionOrgu($list_opt_orgu)
                ->withListOptionText($list_opt_text);
        };
        $object->updateSettings($fnc);

        if (\ilPluginAdmin::isPluginActive('xetr')) {
            $credits = $this->replaceComma($post[self::F_CREDITS]);
            $fnc = function ($s) use ($credits) {
                return $s->withCredits($credits);
            };
            $object->updateSettings($fnc);
        }

        $object->update();

        ilUtil::sendSuccess($this->txt("settings_successful_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT_PROPERTIES);
    }

    /**
     * Replace last comma of value with an dot
     *
     * @param string 	$value
     *
     * @return string
     */
    protected function replaceComma($value)
    {
        if ($value == "") {
            return null;
        }

        $pos = strrpos($value, ",");

        if ($pos !== false) {
            $value = substr_replace($value, ".", $pos, strlen(","));
        }

        return floatval($value);
    }

    /**
     * Inits the form for properties
     *
     * @return ilPropertyFormGUI
     */
    protected function initForm()
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("settings"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextareaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        if (\ilPluginAdmin::isPluginActive('xetr')) {
            $ni = new ilNumberInputGUI($this->txt("credits"), self::F_CREDITS);
            $ni->allowDecimals(true);
            $form->addItem($ni);
        }

        $ci = new ilCheckboxInputGUI($this->txt("list_required"), self::F_LIST_REQUIRED);
        $ci->setInfo($this->txt("list_required_info"));
        $ci->setValue(1);
        $form->addItem($ci);

        $section_fields = new \ilFormSectionHeaderGUI();
        $section_fields->setTitle($this->txt('settings_list_options'));
        $form->addItem($section_fields);
        $opt_orgu = new ilCheckboxInputGUI($this->txt("list_opt_orgu"), self::F_LIST_OPT_ORGU);
        $form->addItem($opt_orgu);
        $opt_txt = new ilCheckboxInputGUI($this->txt("list_opt_text"), self::F_LIST_OPT_TEXT);
        $form->addItem($opt_txt);

        $form->addCommandButton(self::CMD_SAVE_PROPERTIES, $this->txt("save"));
        $form->addCommandButton(self::CMD_EDIT_PROPERTIES, $this->txt("cancel"));

        return $form;
    }

    /**
     * Fill the form with current values
     *
     * @return void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = array();

        $object = $this->actions->getObject();
        $values[self::F_TITLE] = $object->getTitle();
        $values[self::F_DESCRIPTION] = $object->getDescription();

        if (\ilPluginAdmin::isPluginActive('xetr')) {
            $values[self::F_CREDITS] = $object->getSettings()->getCredits();
        }

        $values[self::F_LIST_REQUIRED] = $object->getSettings()->getListRequired();
        $values[self::F_LIST_OPT_ORGU] = $object->getSettings()->getListOptionOrgu();
        $values[self::F_LIST_OPT_TEXT] = $object->getSettings()->getListOptionText();

        $form->setValuesByArray($values);
    }

    /**
     * Tranlsate lang code
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
