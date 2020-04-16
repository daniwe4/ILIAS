<?php
require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
require_once("Services/Form/classes/class.ilTextInputGUI.php");

use CaT\Plugins\AgendaItemPool;

/**
 * Class ilAIPSettingsGUI.
 * GUI for general settings of a AgendaItemPool object.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilAIPSettingsGUI
{
    const CMD_SETTINGS = "showSettings";
    const CMD_SAVE_SETTINGS = "saveSettings";
    const CMD_EDIT_PROPERTIES = "editProperties";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_IS_ONLINE = "is_online";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilObjAgendaItemPoolGUI
     */
    protected $parent_gui;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * Constructor of the class ilAIPSettingsGUI.
     *
     * @return void
     */
    public function __construct(
        ilObjAgendaItemPoolGUI $parent_gui,
        AgendaItemPool\ilObjectActions $object_actions,
        \Closure $txt
    ) {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_usr = $DIC->user();
        $this->parent_gui = $parent_gui;
        $this->object_actions = $object_actions;
        $this->txt = $txt;
    }

    /**
     * Process incomming commands.
     *
     * @return void
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        if ($cmd == null) {
            $cmd = self::CMD_SETTINGS;
        }
        switch ($cmd) {
            case self::CMD_SETTINGS:
            case self::CMD_EDIT_PROPERTIES:
                $this->showSettings();
                break;
            case self::CMD_SAVE_SETTINGS:
                $this->saveSettings();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show the settings gui.
     *
     * @param 	ilPropertyFormGUI 	$form
     * @return 	void
     */
    public function showSettings(ilPropertyFormGUI $form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
            $this->fillForm($form);
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save Settings.
     *
     * @return 	void
     */
    protected function saveSettings()
    {
        $post = $_POST;

        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettings($form);
            return;
        }

        $title = $post[self::F_TITLE];
        $description = $post[self::F_DESCRIPTION];
        $is_online = false;

        if (isset($post[self::F_IS_ONLINE]) && $post[self::F_IS_ONLINE] == 1) {
            $is_online = true;
        }
        $dt = new \DateTime('now', new \DateTimeZone("Europe/Berlin"));
        $usr_id = (int) $this->g_usr->getId();

        $fnc = function ($s) use ($is_online, $dt, $usr_id) {
            $s = $s
                ->withIsOnline($is_online)
                ->withLastChanged($dt)
                ->withLastChangedUsrId($usr_id);
            return $s;
        };

        $obj = $this->object_actions->getObject();
        $obj->setTitle($title);
        $obj->setDescription($description);
        $obj->updateSetting($fnc);
        $obj->update();

        \ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_SETTINGS);
    }

    /**
     * Fill the form with a setting object.
     *
     * @param 	ilPropertyFormGUI 	$form
     * @return 	void
     */
    protected function fillForm(ilPropertyFormGUI $form)
    {
        $obj = $this->object_actions->getObject();
        $arr = [
            self::F_TITLE => $obj->getTitle(),
            self::F_DESCRIPTION => $obj->getDescription(),
            self::F_IS_ONLINE => $obj->getSettings()->getIsOnline()
        ];

        $form->setValuesByArray($arr);
    }

    /**
     * Get the form for the settings gui.
     *
     * @return 	ilPropertyFormGUI
     */
    protected function getForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings"));
        $form->setShowTopButtons(true);
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save_settings"));
        $form->addCommandButton(self::CMD_SETTINGS, $this->txt("cancel"));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $ci = new ilCheckBoxInputGUI($this->txt("settings_online"), self::F_IS_ONLINE);
        $ci->setInfo($this->txt("settings_online_info"));
        $form->addItem($ci);

        return $form;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }
}
