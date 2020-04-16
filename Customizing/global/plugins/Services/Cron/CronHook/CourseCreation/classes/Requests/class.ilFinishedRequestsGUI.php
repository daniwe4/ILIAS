<?php

use CaT\Plugins\CourseCreation\ilActions;
use CaT\Plugins\CourseCreation\Requests\RequestHelper;

require_once("Services/TMS/Table/TMSTableParentGUI.php");

class ilFinishedRequestsGUI extends TMSTableParentGUI
{
    use RequestHelper;

    const CMD_VIEW_ENTRIES = "viewEntries";
    const CRS_ID = "crs_ref_id";

    /**
     * @var \ilCourseCreationPlugin
     */
    protected $plugin;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemlplate
     */
    protected $g_tpl;

    public function __construct(\ilCourseCreationPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->actions = $plugin->getActions();

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_access = $DIC->access();
        $this->g_tree = $DIC->repositoryTree();
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_VIEW_ENTRIES:
                $this->viewEntries();
                break;
            case self::CMD_CANCEL_REQUEST:
                $this->cancelRequest();
                break;
            case self::CMD_CONFIRM_CANCEL_REQUEST:
                $this->confirmCancelRequest();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * List all open requests
     *
     * @return void
     */
    protected function viewEntries()
    {
        $table = $this->getTMSTableGUI();

        $table->determineOffsetAndOrder();
        $table->setTitle($this->txt("tbl_finished_requests"));
        $table->setRowTemplate("tpl.finished_requests.html", $this->plugin->getDirectory());
        $table->setFormAction($this->g_ctrl->getFormAction($this));
        $table->addColumn($this->txt("ref_id"), false);
        $table->addColumn($this->txt("tpl_title"), false);
        $table->addColumn($this->txt("target_ref_id"), false);
        $table->addColumn($this->txt("title"), false);
        $table->addColumn($this->txt("period"), false);
        $table->addColumn($this->txt("created_by"), false);
        $table->addColumn($this->txt("request_ts"), false);
        $table->addColumn($this->txt("finished_ts"), false);
        $table->addColumn($this->txt("actions"), false);
        $table->setMaxCount($this->actions->getCountFinishedRequests());
        $table->setData($this->actions->getFinishedRequests($table->getOffset(), $table->getLimit()));

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $request) {
            $crs_ref_id = $request->getCourseRefId();
            $target_ref_id = $request->getTargetRefId();
            $tpl = $table->getTemplate();

            $tpl_title = "-";
            $target_title = "-";
            $period = "-";
            if (!$this->isDeleted($crs_ref_id)) {
                $obj_id = \ilObject::_lookupObjId($crs_ref_id);
                $tpl_title = $this->getTitleByRefId($crs_ref_id);
            }

            if (!$this->isDeleted($target_ref_id)) {
                $target_crs = $this->getCourse($target_ref_id);
                $target_title = $target_crs->getTitle();

                $start_date = $target_crs->getCourseStart();
                $period = "";
                if (!is_null($start_date)) {
                    $period = $start_date->get(IL_CAL_FKT_DATE, "d.m.Y")
                        . " - "
                        . $target_crs->getCourseEnd()->get(IL_CAL_FKT_DATE, "d.m.Y");
                }
            }

            $tpl->setVariable("REF_ID", $crs_ref_id);
            $tpl->setVariable("TPL_TITLE", $tpl_title);

            $tpl->setVariable("TARGET_REF_ID", $target_ref_id);
            $tpl->setVariable("TITLE", $target_title);
            $tpl->setVariable("PERIOD", $period);

            $tpl->setVariable("CREATED_BY", \ilObjUser::_lookupLogin($request->getUserId()));
            $tpl->setVariable("REQUEST_TS", $request->getRequestedTS()->format("d.m.Y H:i:s"));
            $tpl->setVariable("FINISHED_TS", $request->getFinishedTS()->format("d.m.Y H:i:s"));
            $tpl->setVariable("ACTIONS", $this->getActionMenu($target_ref_id));
        };
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return self::CMD_VIEW_ENTRIES;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        return get_class($this);
    }

    /**
     * Get action menu for each table row
     *
     * @param 	int 	$crs_ref_id
     * @return 	string
     */
    protected function getActionMenu($crs_ref_id)
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($crs_ref_id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $crs_ref_id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($crs_ref_id) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    /**
     * Get items for action menu
     *
     * @param 	int 	$crs_ref_id
     * @return 	array
     */
    protected function getActionMenuItems($crs_ref_id)
    {
        $items = array();

        if ($this->g_access->checkAccess("visible", "", $crs_ref_id)
            && $this->g_access->checkAccess("read", "", $crs_ref_id)
        ) {
            require_once("Services/Link/classes/class.ilLink.php");
            $to_course = ilLink::_getLink($crs_ref_id, "crs");
            ;
            $items[] = array("title" => $this->txt("to_course"), "link" => $to_course, "image" => "", "frame" => "");
        }

        return $items;
    }

    /**
     * Translates code to ilias clean text
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->plugin->txt($code);
    }
}
