<?php
/**
 * Displays the TMS training search
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 * @ilCtrl_Calls	ilTrainingSearchGUI: ilTMSSelfBookingGUI, ilTMSSuperiorBookingGUI, ilTMSSelfBookWaitingGUI, ilTMSSuperiorBookWaitingGUI
 */
class ilTrainingSearchGUI
{
    const CMD_SHOW = "show";

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC["ilCtrl"];
        $this->lng = $DIC["lng"];
        $this->access = $DIC["ilAccess"];

        $this->lng->loadLanguageModule('tms');
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case "iltmsselfbookinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSelfBookingGUI.php");
                $gui = new ilTMSSelfBookingGUI($this, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);
                break;
            case "iltmsselfbookwaitinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSelfBookWaitingGUI.php");
                $gui = new ilTMSSelfBookWaitingGUI($this, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);
                break;
            case "iltmssuperiorbookinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSuperiorBookingGUI.php");
                $gui = new ilTMSSuperiorBookingGUI($this, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);
                break;
            case "iltmssuperiorbookwaitinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSuperiorBookWaitingGUI.php");
                $gui = new ilTMSSuperiorBookWaitingGUI($this, self::CMD_SHOW);
                $this->ctrl->forwardCommand($gui);
                break;
            default:
                $search_object = $this->findSearchObject();
                if (!is_null($search_object)) {
                    $this->redirectToSearch($search_object);
                }
                $this->showNoSearchMessage();
        }
    }

    protected function findSearchObject()
    {
        if (!ilPluginAdmin::isPluginActive("xtrs")) {
            return null;
        }

        $tms_session = new \TMSSession();
        return $tms_session->getCurrentSearch();
    }

    protected function redirectToSearch($search_object)
    {
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingSearch/classes/class.ilObjTrainingSearchGUI.php";
        $this->ctrl->setParameterByClass("ilObjTrainingSearchGUI", "ref_id", $search_object);
        $link = $this->ctrl->getLinkTargetByClass(
            array(
                "ilObjPluginDispatchGUI",
                "ilObjTrainingSearchGUI"
            ),
            ilObjTrainingSearchGUI::CMD_SHOW_CONTENT,
            "",
            false,
            false
        );
        $this->ctrl->setParameterByClass("ilObjTrainingSearchGUI", "ref_id", null);
        ilUtil::redirect($link);
    }

    protected function showNoSearchMessage()
    {
        global $DIC;
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt("header"));

        $ne = new ilNonEditableValueGUI();
        $ne->setValue($this->lng->txt("no_search_object_found"));
        $form->addItem($ne);

        $this->tpl->setContent($form->getHtml());
        $this->tpl->printToStdout();
    }
}
