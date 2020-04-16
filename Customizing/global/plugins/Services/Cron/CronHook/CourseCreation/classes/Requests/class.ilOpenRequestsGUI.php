<?php

use CaT\Plugins\CourseCreation\ilActions;
use CaT\Plugins\CourseCreation\Requests\RequestHelper;

require_once("Services/TMS/Table/TMSTableParentGUI.php");


class ilOpenRequestsGUI extends TMSTableParentGUI
{
    use RequestHelper;

    const CMD_VIEW_ENTRIES = "viewEntries";
    const CMD_CONFIRM_CANCEL_REQUEST = "confirmCancelRequest";
    const CMD_CANCEL_REQUEST = "cancelRequest";
    const REQUEST_ID = "request_id";

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
        $table->setTitle($this->txt("tbl_open_requests"));
        $table->setRowTemplate("tpl.open_requests.html", $this->plugin->getDirectory());
        $table->setFormAction($this->g_ctrl->getFormAction($this));
        $table->addColumn($this->txt("ref_id"), false);
        $table->addColumn($this->txt("tpl_title"), false);
        $table->addColumn($this->txt("title"), false);
        $table->addColumn($this->txt("period"), false);
        $table->addColumn($this->txt("created_by"), false);
        $table->addColumn($this->txt("request_ts"), false);
        $table->addColumn($this->txt("actions"), false);
        $table->setMaxCount($this->actions->getCountOpenRequests());
        $table->setData($this->actions->getOpenRequests($table->getOffset(), $table->getLimit()));

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Get the closure is table should be filled with
     *
     * @return \Closure
     */
    protected function fillRow()
    {
        return function ($table, $request) {
            $crs_ref_id = $request->getCourseRefId();
            $configs = $request->getConfigurationFor($crs_ref_id);
            $tpl = $table->getTemplate();
            $tpl->setVariable("REF_ID", $crs_ref_id);

            $tpl_title = "-";
            if (!$this->isDeleted($crs_ref_id)) {
                $obj_id = \ilObject::_lookupObjId($crs_ref_id);
                $tpl_title = $this->getTitleByRefId($crs_ref_id);
            }

            $tpl->setVariable("TPL_TITLE", $tpl_title);

            foreach ($configs as $config) {
                if (array_key_exists("course_period", $config)) {
                    $start = new \ilDate($config["course_period"]["start"], IL_CAL_DATE);
                    $end = new \ilDate($config["course_period"]["end"], IL_CAL_DATE);
                    $period = $start->get(IL_CAL_FKT_DATE, "d.m.Y") . " - " . $end->get(IL_CAL_FKT_DATE, "d.m.Y");
                    $tpl->setVariable("PERIOD", $period);
                }

                if (array_key_exists("title", $config)) {
                    $tpl->setVariable("TITLE", $config["title"]);
                }
            }

            $tpl->setVariable("CREATED_BY", \ilObjUser::_lookupLogin($request->getUserId()));
            $tpl->setVariable("REQUEST_TS", $request->getRequestedTS()->format("d.m.Y H:i:s"));
            $tpl->setVariable("ACTIONS", $this->getActionMenu($request->getId()));
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
     * @param 	int 	$id
     * @param 	bool 	$is_blank
     * @return 	string
     */
    protected function getActionMenu($id)
    {
        include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new \ilAdvancedSelectionListGUI();
        $current_selection_list->setAsynch(false);
        $current_selection_list->setAsynchUrl(true);
        $current_selection_list->setListTitle($this->txt("actions"));
        $current_selection_list->setId($id);
        $current_selection_list->setSelectionHeaderClass("small");
        $current_selection_list->setItemLinkClass("xsmall");
        $current_selection_list->setLinksMode("il_ContainerItemCommand2");
        $current_selection_list->setHeaderIcon(\ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
        $current_selection_list->setUseImages(false);
        $current_selection_list->setAdditionalToggleElement("id" . $id, "ilContainerListItemOuterHighlight");

        foreach ($this->getActionMenuItems($id) as $key => $value) {
            $current_selection_list->addItem($value["title"], "", $value["link"], $value["image"], "", $value["frame"]);
        }

        return $current_selection_list->getHTML();
    }

    /**
     * Get items for action menu
     *
     * @param 	int 	$id
     * @return 	array
     */
    protected function getActionMenuItems($id)
    {
        $this->g_ctrl->setParameter($this, self::REQUEST_ID, $id);
        $link_cancel = $this->g_ctrl->getLinkTarget($this, self::CMD_CONFIRM_CANCEL_REQUEST);
        $this->g_ctrl->setParameter($this, self::REQUEST_ID, null);

        $items = array();
        $items[] = array("title" => $this->txt("cancel_request"), "link" => $link_cancel, "image" => "", "frame" => "");

        return $items;
    }

    /**
     * Show confirmation form for cancel
     *
     * @return void
     */
    protected function confirmCancelRequest()
    {
        $get = $_GET;
        if (isset($get[self::REQUEST_ID]) && is_numeric($get[self::REQUEST_ID])) {
            $request_id = $_GET[self::REQUEST_ID];
            require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
            $confirmation = new \ilConfirmationGUI();
            $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
            $confirmation->setHeaderText($this->txt("confirm_cancel_request"));
            $confirmation->setCancel($this->txt("cancel"), self::CMD_VIEW_ENTRIES);
            $confirmation->setConfirm($this->txt("cancel_request"), self::CMD_CANCEL_REQUEST);

            $confirmation->addHiddenItem(self::REQUEST_ID, $request_id);
            $this->g_tpl->setContent($confirmation->getHTML());
        } else {
            \ilUtil::sendInfo($this->txt("no_request_id"), true);
            $this->g_ctrl->redirect($this, self::CMD_VIEW_ENTRIES);
        }
    }

    /**
     * Deletes an open request
     *
     * @return void
     */
    protected function cancelRequest()
    {
        $post = $_POST;
        if (isset($post[self::REQUEST_ID]) && is_numeric($post[self::REQUEST_ID])) {
            $request_id = (int) $post[self::REQUEST_ID];
            $this->actions->setRequestFinished($request_id, new \DateTime());
            \ilUtil::sendSuccess($this->txt("request_canceld"), true);
        } else {
            \ilUtil::sendInfo($this->txt("no_request_id"), true);
        }
        $this->g_ctrl->redirect($this, self::CMD_VIEW_ENTRIES);
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
