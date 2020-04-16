<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

/**
 * @ilCtrl_Calls ilScheduleOverviewGUI: ilScheduleGUI, ilCertificateConfigurationGUI, ilConfigurationGUI
 * @ilCtrl_Calls ilScheduleOverviewGUI: ilActivationGUI, ilParticipationDocumentGUI
 */
class ilScheduleOverviewGUI extends TMSTableParentGUI
{
    const TABLE_ID = "ov_cert_schedules";
    const CMD_SHOW = "showSchedules";
    const S_TAB_OVERVIEW = "certificates_overview";
    const S_TAB_CONFIGURATION = "acc_doc_configuration";
    const S_TAB_PART_DOCUMENT_IMAGE = "part_document_image";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var Schedules\DB
     */
    protected $db;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var ilScheduleGUI
     */
    protected $schedule_gui;

    /**
     * @var ilCertificateConfigurationGUI
     */
    protected $certificate_gui;

    /**
     * @var string
     */
    protected $add_schedule_link;

    /**
     * @var string
     */
    protected $edit_schedule_link;

    /**
     * @var string
     */
    protected $delete_schedule_link;

    /**
     * @var string
     */
    protected $edit_certificate_link;

    /**
     * @var ilActivationGUI
     */
    protected $configuration_gui;

    /**
     * @var string
     */
    protected $configuration_link;

    /**
     * @var string
     */
    protected $overview_link;

    /**
     * @var string
     */
    protected $part_document_link;

    /**
     * @var ilParticipationDocumentGUI
     */
    protected $part_document_gui;


    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilToolbarGUI $toolbar,
        ilTabsGUI $tabs,
        Schedules\DB $db,
        Closure $txt,
        string $directory,
        ilScheduleGUI $schedule_gui,
        ilCertificateConfigurationGUI $certificate_gui,
        string $add_schedule_link,
        string $edit_schedule_link,
        string $delete_schedule_link,
        string $edit_certificate_link,
        ilActivationGUI $configuration_gui,
        string $configuration_link,
        string $overview_link,
        string $part_document_link,
        ilParticipationDocumentGUI $part_document_gui
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->tabs = $tabs;
        $this->db = $db;
        $this->txt = $txt;
        $this->directory = $directory;
        $this->schedule_gui = $schedule_gui;
        $this->certificate_gui = $certificate_gui;
        $this->add_schedule_link = $add_schedule_link;
        $this->edit_schedule_link = $edit_schedule_link;
        $this->delete_schedule_link = $delete_schedule_link;
        $this->edit_certificate_link = $edit_certificate_link;
        $this->configuration_gui = $configuration_gui;
        $this->configuration_link = $configuration_link;
        $this->overview_link = $overview_link;
        $this->part_document_link = $part_document_link;
        $this->part_document_gui = $part_document_gui;
    }

    /**
     * @throws ilCtrlException if command forward failed
     * @throws Exception if command is not known
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "ilschedulegui":
                $this->forwardScheduleGUI();
                break;
            case "ilcertificateconfigurationgui":
                $this->forwardCertificateGUI();
                break;
            case "ilactivationgui":
                $this->setSubTabs(self::S_TAB_CONFIGURATION);
                $this->forwardConfigurationGUI();
                break;
            case "ilparticipationdocumentgui":
                $this->setSubTabs(self::S_TAB_PART_DOCUMENT_IMAGE);
                $this->forwardPartDocumentGUI();
                break;
            default:
                $cmd = $this->ctrl->getCmd();
                switch ($cmd) {
                    case self::CMD_SHOW:
                        $this->setSubTabs(self::S_TAB_OVERVIEW);
                        $this->setToolbar();
                        $this->showSchedules();
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardScheduleGUI()
    {
        $this->ctrl->forwardCommand($this->schedule_gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardCertificateGUI()
    {
        $this->ctrl->forwardCommand($this->certificate_gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardConfigurationGUI()
    {
        $this->ctrl->forwardCommand($this->configuration_gui);
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardPartDocumentGUI()
    {
        $this->ctrl->forwardCommand($this->part_document_gui);
    }

    protected function setToolbar()
    {
        $btn = ilLinkButton::getInstance();
        $btn->setUrl($this->add_schedule_link);
        $btn->setCaption($this->txt("add_schedule"), false);
        $this->toolbar->addButtonInstance($btn);
    }

    protected function showSchedules()
    {
        $table = $this->getTMSTableGUI();
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setRowTemplate("tpl.schedules.html", $this->directory);
        $table->setTitle($this->txt("schedules"));
        $table->setDefaultOrderField("title");

        $table->determineOffsetAndOrder();
        $table->determineLimit();

        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();
        $limit = (int) $table->getLimit();
        $offset = $table->getOffset();

        $table->addColumn($this->txt("title"), "title");
        $table->addColumn($this->txt("schedule_from"), "start");
        $table->addColumn($this->txt("schedule_to"), "end");
        $table->addColumn($this->txt("part_document"), "part_document");
        $table->addColumn($this->txt("active"), "active");
        $table->addColumn($this->txt("actions"));

        $data = $this->db->selectAllBy($order_field, $order_direction, $limit, $offset);
        $table->setData($data);

        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Get the closure table should be filled with
     *
     * @return \Closure
     */
    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, Schedules\Schedule $schedule) {
            $tpl = $table->getTemplate();

            $tpl->setVariable("TITLE", $schedule->getTitle());
            $tpl->setVariable("SCHEDULE_FROM", $schedule->getStart()->format("d.m.Y"));
            $tpl->setVariable("SCHEDULE_TO", $schedule->getEnd()->format("d.m.Y"));

            $active = $this->txt("no");
            if ($schedule->isParticipationsDocumentActive()) {
                $active = $this->txt("yes");
            }
            $tpl->setVariable("PART_DOCUMENT", $active);

            $active = $this->txt("no");
            if ($schedule->isActive()) {
                $active = $this->txt("yes");
            }
            $tpl->setVariable("ACTIVE", $active);
            $tpl->setVariable("ACTIONS", $this->buildActionMenu($schedule->getId()));
        };
    }

    protected function buildActionMenu(int $id) : string
    {
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

    protected function getActionMenuItems($id)
    {
        $items = [];

        $link = $this->edit_schedule_link . "&" . ilScheduleGUI::URL_PARAM . "=" . $id;
        $items[] = array("title" => $this->txt("edit_schedule"), "link" => $link, "image" => "", "frame" => "");

        $link = $this->edit_certificate_link . "&" . ilScheduleGUI::URL_PARAM . "=" . $id;
        $items[] = array("title" => $this->txt("edit_certificate"), "link" => $link, "image" => "", "frame" => "");

        $link = $this->delete_schedule_link . "&" . ilScheduleGUI::URL_PARAM . "=" . $id;
        $items[] = array("title" => $this->txt("delete_schedule"), "link" => $link, "image" => "", "frame" => "");

        return $items;
    }

    /**
     * Get the basic command table has to use
     *
     * @return string
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW;
    }

    /**
     * Get the id of table
     *
     * @return string
     */
    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function setSubTabs(string $active_sub_tab)
    {
        $this->tabs->addSubTab(
            self::S_TAB_OVERVIEW,
            $this->txt("schedules"),
            $this->overview_link
        );
        $this->tabs->addSubTab(
            self::S_TAB_CONFIGURATION,
            sprintf($this->txt("activation"), $this->txt("certificates")),
            $this->configuration_link
        );
        $this->tabs->addSubTab(
            self::S_TAB_PART_DOCUMENT_IMAGE,
            $this->txt("part_document_image"),
            $this->part_document_link
        );

        $this->tabs->activateSubtab($active_sub_tab);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
