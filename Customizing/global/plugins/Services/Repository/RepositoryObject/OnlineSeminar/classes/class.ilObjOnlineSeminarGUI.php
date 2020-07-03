<?php
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
require_once(__DIR__ . "/Settings/class.ilOnlineSeminarGUI.php");
require_once(__DIR__ . "/Participant/class.ilParticipantGUI.php");
require_once(__DIR__ . "/LPSettings/class.ilLPSettingsGUI.php");

use CaT\Libs\ExcelWrapper\Spout;

/**
 * Plugin object GUI class. Baseclass for all GUI action in ILIAS
 *
 * @ilCtrl_isCalledBy ilObjOnlineSeminarGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjOnlineSeminarGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjOnlineSeminarGUI: ilOnlineSeminarGUI, ilParticipantGUI, ilRepositorySearchGUI, ilLPSettingsGUI, ilExportGUI
 */
class ilObjOnlineSeminarGUI extends ilObjectPluginGUI
{
    const F_VC_TYPE = "f_vc_type";
    const TAB_LP = "tab_lp";

    /**
     * Set in ilObject2GUI constructor
     *
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * May be set in ilObject2GUI constructor
     *
     * @var ilAccess
     */
    protected $access_handler;

    /**
     * Set in ilObject2GUI constructor
     *
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilObjUser
     */
    protected $g_user;

    /**
     * @var
     */
    protected $writer;

    /**
     * @var
     */
    protected $interpreter;
    /**
     * Called after parent constructor. It's possible to define some plugin special values
     */
    protected function afterConstructor()
    {
        global $DIC;
        $this->g_user = $DIC->user();

        if ($this->access_handler === null) {
            $this->access_handler = $DIC->access();
        }

        $this->xlsx_writer = new Spout\SpoutWriter();
        $this->csv_writer = new Spout\SpoutCSVWriter();
        $this->interpreter = new Spout\SpoutInterpreter();

        if ($this->object !== null) {
            $this->vc_actions = $this->object->getVCActions();
        }
    }

    /**
    * Get type.  Same value as choosen in plugin.php
    */
    final public function getType()
    {
        return "xwbr";
    }

    /**
    * Handles all commmands of this class, centralizes permission checks
    */
    public function performCommand($cmd)
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilrepositorysearchgui":
                $this->activateTab($cmd);
                require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
                $rep_search = new ilRepositorySearchGUI();
                $gui = new ilParticipantGUI($this->object->getActions(), $this->vc_actions, $this->xlsx_writer, $this->csv_writer, $this->interpreter);
                $this->ctrl->setReturn($this, ilParticipantGUI::CMD_SHOW_PARTICIPANTS);
                $rep_search->setCallback($gui, ilParticipantGUI::CMD_ADD_USER_FROM_SEARCH);
                $this->ctrl->forwardCommand($rep_search);
                break;
            case "ilonlineseminargui":
                $this->activateTab(ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES);
                $vc_form_helper = $this->object->getVCFormHelper();
                $gui = new ilOnlineSeminarGUI($this->object->getActions(), $this->vc_actions, $vc_form_helper);
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilparticipantgui":
                $this->activateTab(ilParticipantGUI::CMD_SHOW_PARTICIPANTS);
                $gui = new ilParticipantGUI($this->object->getActions(), $this->vc_actions, $this->xlsx_writer, $this->csv_writer, $this->interpreter);
                $this->ctrl->forwardCommand($gui);
                break;
            case "illpsettingsgui":
                $this->activateTab(ilLPSettingsGUI::CMD_LP);
                $gui = new ilLPSettingsGUI($this, $this->object->getActions());
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES:
                        $this->redirectOnlineSeminar();
                        break;
                    case ilParticipantGUI::CMD_SHOW_PARTICIPANTS:
                        if ($this->access_handler->checkAccess("edit_member", "", $this->object->getRefId())) {
                            $this->redirectParticipant();
                            break;
                        } else {
                            $this->ctrl->setCmd("showSummary");
                            $this->ctrl->setCmdClass("ilinfoscreengui");
                            $this->infoScreen();
                        }
                        break;
                    case ilParticipantGUI::CMD_SHOW_CONTENT:
                        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
                            $this->redirectOnlineSeminar();
                            break;
                        } elseif ($this->access_handler->checkAccess("edit_member", "", $this->object->getRefId())) {
                            $this->redirectParticipant();
                            break;
                        } else {
                            $this->ctrl->setCmd("showSummary");
                            $this->ctrl->setCmdClass("ilinfoscreengui");
                            $this->infoScreen();
                        }
                        break;
                    default:
                        throw new Exception(__METHOD__ . " unknown command " . $cmd);
                }
        }
    }

    /**
    * After object has been created -> jump to this command
    */
    public function getAfterCreationCmd()
    {
        return ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES;
    }

    /**
    * Get standard command
    */
    public function getStandardCmd()
    {
        return ilParticipantGUI::CMD_SHOW_CONTENT;
    }

    /**
     * @inhertidoc
     */
    public function initCreateForm($a_new_type)
    {
        $form = parent::initCreateForm($a_new_type);
        $plugin_actions = $this->plugin->getActions();

        $si = new ilSelectInputGUI($this->plugin->txt("vc_type"), self::F_VC_TYPE);
        $options = array(null => $this->plugin->txt("please_select"));
        $vcs = $plugin_actions->getAvailableVCTypes();
        foreach ($vcs as $vc) {
            $options[$vc] = $this->plugin->txt($vc);
        }
        $si->setOptions($options);
        $si->setRequired(true);
        $form->addItem($si);

        return $form;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(\ilObject $newObj)
    {
        $post = $_POST;

        $fnc = function (CaT\Plugins\OnlineSeminar\Settings\OnlineSeminar $s) use ($post) {
            return $s->withVCType($post[self::F_VC_TYPE]);
        };
        $newObj->updateSettings($fnc);
        $newObj->update();

        $vc_actions = $newObj->getVCActions();
        $vc_actions->create();


        parent::afterSave($newObj);
    }

    /**
     * Redirect to online seminar gui
     *
     * @return null
     */
    protected function redirectOnlineSeminar()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjOnlineSeminarGUI", "ilOnlineSeminarGUI"),
            ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Redirect to online seminar gui
     *
     * @return null
     */
    protected function redirectParticipant()
    {
        $link = $this->ctrl->getLinkTargetByClass(
            array("ilObjOnlineSeminarGUI", "ilParticipantGUI"),
            ilParticipantGUI::CMD_SHOW_PARTICIPANTS,
            "",
            false,
            false
        );
        \ilUtil::redirect($link);
    }

    /**
     * Set the tabs for the site and activate current
     *
     * @return null
     */
    protected function setTabs()
    {
        $this->addInfoTab();

        if ($this->access_handler->checkAccess("edit_member", "", $this->object->getRefId())) {
            $participant = $this->ctrl->getLinkTargetByClass(array("ilObjOnlineSeminarGUI", "ilParticipantGUI"), ilParticipantGUI::CMD_SHOW_PARTICIPANTS);
            $this->tabs_gui->addTab(ilParticipantGUI::CMD_SHOW_PARTICIPANTS, $this->txt("tab_member"), $participant);
        }

        if ($this->access_handler->checkAccess("write", "", $this->object->getRefId())) {
            $settings = $this->ctrl->getLinkTargetByClass(array("ilObjOnlineSeminarGUI", "ilOnlineSeminarGUI"), ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES);
            $this->tabs_gui->addTab(ilOnlineSeminarGUI::CMD_EDIT_PROPERTIES, $this->txt("tab_settings"), $settings);
        }

        if ($this->access_handler->checkAccess("edit_participation", "", $this->object->getRefId())) {
            $lp = $this->ctrl->getLinkTargetByClass(array("ilObjOnlineSeminarGUI", "ilLPSettingsGUI"), ilLPSettingsGUI::CMD_LP);
            $this->tabs_gui->addTab(ilLPSettingsGUI::CMD_LP, $this->plugin->txt("tab_lp"), $lp);
        }

        $this->addExportTab();

        $this->addPermissionTab();
    }

    /**
    * @inheritdoc
    */
    public function addInfoItems($info)
    {
        $settings = $this->object->getSettings();
        $actions = $this->object->getActions();

        $begin = $settings->getBeginning();
        $schedule = "-";
        if ($begin != null) {
            $schedule = $begin->get(IL_CAL_FKT_DATE, "d.m.Y H:i:s");
            $schedule .= " - " . $settings->getEnding()->get(IL_CAL_FKT_DATE, "d.m.Y H:i:s");
        }

        $info->addSection($this->txt("informations"));
        $info->addProperty($this->txt("title"), $this->object->getTitle());
        $info->addProperty($this->txt("descripton"), $this->strintToPlaceholder($this->object->getDescription()));
        $info->addProperty($this->txt("url"), $this->strintToPlaceholder($settings->getUrl()));
        $info->addProperty($this->txt("schedule"), $schedule);

        $booked = $actions->isBookedUser((int) $this->g_user->getId());
        $tutor = $actions->isTutor((int) $this->g_user->getId());
        if ($booked) {
            $vc_form_helper = $this->object->getVCFormHelper();
            $vc_form_helper->addInfoProperties($info, true, $tutor);
        } elseif ($tutor) {
            $vc_form_helper = $this->object->getVCFormHelper();
            $vc_form_helper->addInfoProperties($info, false, true);
        } else {
            if ($settings->getAdmission() == "self_booking") {
                global $DIC;
                $f = $DIC->ui()->factory();
                $renderer = $DIC->ui()->renderer();

                $link = $this->ctrl->getLinkTargetByClass(array("ilObjOnlineSeminarGUI", "ilParticipantGUI"), ilParticipantGUI::CMD_SELF_BOOK_USER);
                $btn = $renderer->render($f->button()->standard($this->txt("enter"), $link));
                $info->addProperty($this->txt("book"), $btn);
            }
        }
    }

    /**
     * Changes emoty string to "-"
     *
     * @param string 	$string
     *
     * @return string
     */
    public function strintToPlaceholder($string)
    {
        if ($string == "" || $string == null) {
            return "-";
        }

        return $string;
    }

    /**
     * activate current tab
     *
     * @param string 	$cmd
     *
     * @return null
     */
    protected function activateTab($cmd)
    {
        $this->tabs_gui->activateTab($cmd);
    }
}
