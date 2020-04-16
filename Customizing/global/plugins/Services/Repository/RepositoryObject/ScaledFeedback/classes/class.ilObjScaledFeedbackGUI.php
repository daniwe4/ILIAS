<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "./Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once __DIR__ . "/LPSettings/class.ilScaledFeedbackLPSettingsGUI.php";
require_once __DIR__ . "/Settings/class.ilSFSettingsGUI.php";
require_once __DIR__ . "/Feedback/class.ilFeedbackGUI.php";
require_once __DIR__ . "/Evaluation/class.ilEvaluationGUI.php";

use CaT\Plugins\ScaledFeedback\DI;

/**
 * @ilCtrl_isCalledBy ilObjScaledFeedbackGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjScaledFeedbackGUI: ilAdministrationGUI
 * @ilCtrl_isCalledBy ilObjScaledFeedbackGUI: ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjScaledFeedbackGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjScaledFeedbackGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjScaledFeedbackGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjScaledFeedbackGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_calls ilObjScaledFeedbackGUI: ilFeedbackGUI
 * @ilCtrl_calls ilObjScaledFeedbackGUI: ilScaledFeedbackLPSettingsGUI
 * @ilCtrl_calls ilObjScaledFeedbackGUI: ilEvaluationGUI
 * @ilCtrl_calls ilObjScaledFeedbackGUI: ilSFSettingsGUI
 */
class ilObjScaledFeedbackGUI extends ilObjectPluginGUI
{
    use DI;

    const TAB_FEEDBACK = "feedback";
    const TAB_SETTINGS = "settings";
    const TAB_EVALUATION = "evaluation";
    const TAB_PERMISSIONS = "permissions";
    const TAB_LP_SETTINGS = "learning_progress";

    const CMD_PERMISSIONS = "showPermissions";
    const CMD_SHOW = "showContent";
    const CMD_INFO = "infoScreen";

    const F_SET_ID = "set_id";

    /**
     * @var Pimple\Container
     */
    protected $dic;

    protected function afterConstructor()
    {
        global $DIC;

        if (!is_null($this->object)) {
            $this->dic = $this->getObjectDIC($this->object, $DIC);
            $this->dic["lng"]->loadLanguageModule("xfbk");
        }
    }

    final public function getType()
    {
        return "xfbk";
    }

    /**
     * @throws Exception
     */
    public function performCommand()
    {
        $cmd = $this->dic["ilCtrl"]->getCMD();

        if ($cmd == null) {
            $cmd = self::CMD_SHOW;
        }

        $next_class = $this->dic["ilCtrl"]->getNextClass('ilsettingsgui');

        switch ($next_class) {
            case 'ilsfsettingsgui':
                $this->forwardSettings();
                break;
            case 'ilfeedbackgui':
                $this->forwardFeedback();
                break;
            case "ilscaledfeedbacklpsettingsgui":
                $this->forwardLPSettings();
                break;
            case "ilevaluationgui":
                if (!$this->dic["ilAccess"]->checkAccess(
                    "view_evaluation",
                    "",
                    $this->object->getRefId()
                )
                ) {
                    $this->dic["ilCtrl"]->redirect($this, self::CMD_INFO);
                }
                $this->forwardEvaluation();
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
                            $this->forwardSettings();
                            break;
                        }
                        if (
                            !$this->dic["ilAccess"]->checkAccess("read", "", $this->object->getRefId()) ||
                            !$this->object->getSettings()->getOnline()
                        ) {
                            $this->dic["ilCtrl"]->redirect($this, self::CMD_INFO);
                        }
                        $this->dic["ilCtrl"]->setCmd(self::CMD_SHOW);
                        $this->forwardFeedback();
                        break;
                    default:
                        throw new Exception("ilObjScaledFeedbackGUI:: Unknown command: " . $cmd);
                }
        }
    }

    public function getAfterCreationCmd()
    {
        return self::CMD_SHOW;
    }

    public function getStandardCmd()
    {
        return self::CMD_SHOW;
    }

    /**
     * @inherit
     */
    public function afterSave(\ilObject $newObj)
    {
        $set_id = (int) $_POST['set_id'];
        $fnc = function ($s) use ($set_id) {
            $s = $s
                ->withSetId($set_id);
            return $s;
        };

        $newObj->updateSettings($fnc);
        $newObj->update();

        parent::afterSave($newObj);
    }

    /**
     * @inhertidoc
     */
    public function initCreateForm($a_new_type)
    {
        global $DIC;

        $form = parent::initCreateForm($a_new_type);
        $dic = $this->getPluginDIC($this->plugin, $DIC);

        $si = new ilSelectInputGUI($this->plugin->txt("question_set"), self::F_SET_ID);
        $options = array(null => $this->plugin->txt("please_select"));
        $options = $options + $dic["config.db"]->getQuestionSetValues();
        $si->setOptions($options);
        $si->setRequired(true);
        $form->addItem($si);

        return $form;
    }

    /**
     * @throws ilCtrlException
     */
    public function forwardFeedback()
    {
        $this->tabs->activateTab(self::TAB_FEEDBACK);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["feedback.gui"]);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardSettings()
    {
        $this->tabs->activateTab(self::TAB_SETTINGS);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["settings.gui"]);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardEvaluation()
    {
        $this->tabs->activateTab(self::TAB_EVALUATION);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["evaluation.gui"]);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardLPSettings()
    {
        $this->tabs->activateTab(self::TAB_LP_SETTINGS);
        $this->dic["ilCtrl"]->forwardCommand($this->dic["lpsettings.gui"]);
    }

    /**
     * Set the tabs for the site.
     *
     * @return 	void
     */
    protected function setTabs()
    {
        $this->addInfoTab();

        $feedback = $this->dic["feedback.gui.link"];
        $settings = $this->dic["settings.gui.link"];
        $evaluation = $this->dic["evaluation.gui.link"];
        $lp = $this->dic["lpsettings.gui.link"];

        if (
            $this->dic["ilAccess"]->checkAccess("read", "", $this->object->getRefId()) &&
            $this->object->getSettings()->getOnline()
        ) {
            $this->tabs->addTab(self::TAB_FEEDBACK, $this->txt("feedback"), $feedback);
        } elseif ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_FEEDBACK, $this->txt("feedback"), $feedback);
        }

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_SETTINGS, $this->txt("settings"), $settings);
        }

        if ($this->dic["ilAccess"]->checkAccess("write", "", $this->object->getRefId())) {
            $this->tabs_gui->addTab(self::TAB_LP_SETTINGS, $this->plugin->txt("tab_lp"), $lp);
        }

        if ($this->dic["ilAccess"]->checkAccess("view_evaluation", "", $this->object->getRefId())) {
            $this->tabs->addTab(self::TAB_EVALUATION, $this->txt("evaluation"), $evaluation);
        }

        $this->addPermissionTab();
    }
}
