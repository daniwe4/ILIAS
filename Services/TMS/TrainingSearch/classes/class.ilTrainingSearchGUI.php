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
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilPersonalDesktopGUI
     */
    protected $parent;

    /**
     * @var TrainingSearchDB
     */
    protected $db;

    public function __construct()
    {
        global $DIC;

        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_lng = $DIC->language();
        $this->g_access = $DIC["ilAccess"];

        $this->g_lng->loadLanguageModule('tms');
    }

    public function executeCommand()
    {
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "iltmsselfbookinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSelfBookingGUI.php");
                $gui = new ilTMSSelfBookingGUI($this, self::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltmsselfbookwaitinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSelfBookWaitingGUI.php");
                $gui = new ilTMSSelfBookWaitingGUI($this, self::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltmssuperiorbookinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSuperiorBookingGUI.php");
                $gui = new ilTMSSuperiorBookingGUI($this, self::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
                break;
            case "iltmssuperiorbookwaitinggui":
                require_once("Services/TMS/Booking/classes/class.ilTMSSuperiorBookWaitingGUI.php");
                $gui = new ilTMSSuperiorBookWaitingGUI($this, self::CMD_SHOW);
                $this->g_ctrl->forwardCommand($gui);
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

        $xtrs_objects = ilObject::_getObjectsDataForType("xtrs", true);

        if (count($xtrs_objects) == 0) {
            return null;
        }

        uasort($xtrs_objects, function ($a, $b) {
            return strcmp($a["id"], $b["id"]);
        });

        foreach ($xtrs_objects as $value) {
            foreach (ilObject::_getAllReferences($value["id"]) as $ref_id) {
                if (
                    $this->g_access->checkAccess("visible", "", $ref_id) &&
                    $this->g_access->checkAccess("read", "", $ref_id) &&
                    $this->g_access->checkAccess("use_search", "", $ref_id)
                ) {
                    return $ref_id;
                }
            }
        }

        return null;
    }

    protected function redirectToSearch($search_object)
    {
        require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingSearch/classes/class.ilObjTrainingSearchGUI.php";
        $this->g_ctrl->setParameterByClass("ilObjTrainingSearchGUI", "ref_id", $search_object);
        $link = $this->g_ctrl->getLinkTargetByClass(
            array(
                "ilObjPluginDispatchGUI",
                "ilObjTrainingSearchGUI"
            ),
            ilObjTrainingSearchGUI::CMD_SHOW_CONTENT,
            "",
            false,
            false
        );
        $this->g_ctrl->setParameterByClass("ilObjTrainingSearchGUI", "ref_id", null);
        ilUtil::redirect($link);
    }

    protected function showNoSearchMessage()
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->g_lng->txt("header"));

        $ne = new ilNonEditableValueGUI();
        $ne->setValue($this->g_lng->txt("no_search_object_found"));
        $form->addItem($ne);

        $this->g_tpl->setContent($form->getHtml());
        $this->g_tpl->show();
    }
}
