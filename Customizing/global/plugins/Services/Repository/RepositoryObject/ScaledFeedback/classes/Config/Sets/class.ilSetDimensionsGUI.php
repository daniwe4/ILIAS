<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "Services/Form/classes/class.ilFormSectionHeaderGUI.php";
require_once "Services/Form/classes/class.ilTextInputGUI.php";

use \CaT\Plugins\ScaledFeedback;
use CaT\Plugins\ScaledFeedback\Config\DB;
use \CaT\Plugins\ScaledFeedback\Config\Dimensions\Dimension;

class ilSetDimensionsGUI extends TMSTableParentGUI
{
    const CMD_SHOW_SET_DIMENSIONS = "showSetDimensions";
    const CMD_ADD_SET_DIMENSION = "addSetDimension";
    const CMD_SAVE_SET_DIMENSION = "saveSetDimension";
    const CMD_DELETE_SET_DIMENSION = "deleteSetDimension";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_CANCEL = "cancel";

    const TABLE_ID = "set_dimensions";

    const POST_VAR = "row_selector";

    const F_SELECT = "select";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var DB
     */
    protected $db;

    /**
     * @var string
     */
    protected $cancel_link;

    /**
     * @var string
     */
    protected $plugin_path;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilToolbarGUI $toolbar,
        ilLanguage $lng,
        DB $db,
        string $cancel_link,
        string $plugin_path,
        Closure $txt
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->lng = $lng;
        $this->db = $db;
        $this->cancel_link = $cancel_link;
        $this->plugin_path = $plugin_path;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        $this->tpl->setTitle(
            $this->lng->txt("cmps_plugin") . ": " . $_GET["pname"] . " Set: " . $this->getSetTitle()
        );

        switch ($cmd) {
            case self::CMD_SHOW_SET_DIMENSIONS:
                $this->showContent();
                break;
            case self::CMD_ADD_SET_DIMENSION:
                $this->addSetDimension();
                break;
            case self::CMD_SAVE_SET_DIMENSION:
                $this->saveSetDimension();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE_SET_DIMENSION:
                $this->deleteSetDimension();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function showContent()
    {
        if ($this->isSetInUse()) {
            ilUtil::sendInfo($this->txt('set_in_use'), true);
        }
        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $this->setToolbar($set);
        $this->renderTable($set->getDimensions());
    }


    protected function addSetDimension()
    {
        $post = $_POST;

        $sel_dim = $post[self::F_SELECT];
        if (is_null($sel_dim) || $sel_dim == "") {
            ilUtil::sendInfo($this->txt("no_dim_selected"));
            $this->showContent();
            return;
        }

        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $dim = $this->db->selectDimensionById((int) $sel_dim);
        $top_ordernumber = $this->db->getHighestOrdernumber($this->getValidatedIdFromGet());
        $dim = $dim->withOrdernumber($top_ordernumber + 10);
        $set = $set->withAdditionalDimension($dim);
        $this->db->updateSet($set);
        $this->showContent();
    }

    protected function saveSetDimension()
    {
        $dims = array();
        $post = $_POST;

        $orders = $post['order'];
        $dim_ids = $post['dim_ids'];

        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        $set->resetDimensions();

        for ($i = 0;$i < count($orders);$i++) {
            $dim = $this->db->selectDimensionById((int) $dim_ids[$i]);
            $dim = $dim->withOrdernumber((int) $orders[$i]);
            $dims[] = $dim;
        }

        $dims = $this->sortDimensions($dims);
        foreach ($dims as $dim) {
            $set = $set->withAdditionalDimension($dim);
        }

        $this->db->updateSet($set);
        ilUtil::sendSuccess($this->txt("edit_successful"), true);
        $this->ctrl->setParameter($this, "id", $set->getSetId());
        $this->ctrl->redirect($this, self::CMD_SHOW_SET_DIMENSIONS);
    }

    protected function deleteSetDimension()
    {
        $post = $_POST;

        $set = $this->db->selectSetById($this->getValidatedIdFromGet());
        foreach ($post['delete'] as $id) {
            $set = $set->withRemovedDimensionById((int) $id);
        }

        $dims = $this->sortDimensions($set->getDimensions());
        foreach ($dims as $dim) {
            $set = $set->withAdditionalDimension($dim);
        }
        $this->db->updateSet($set);

        ilUtil::sendSuccess($this->txt('entries_delete'), true);
        $this->showContent();
    }

    protected function cancel()
    {
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function confirmDelete()
    {
        $post = $_POST;

        if (!isset($post['row_selector'])) {
            ilUtil::sendInfo($this->txt('no_entries_delete'), true);
            $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
            $this->ctrl->redirect($this, self::CMD_SHOW_SET_DIMENSIONS);
        }

        require_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new ilConfirmationGUI();

        foreach ($post['row_selector'] as $value) {
            $confirmation->addHiddenItem('delete[]', $value);
        }

        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $confirmation->setHeaderText($this->txt("delete_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_DELETE_SET_DIMENSION);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function setToolbar(ScaledFeedback\Config\Sets\Set $set)
    {
        if ($this->isSetInUse()) {
            return;
        }
        $si = new ilSelectInputGUI("", self::F_SELECT);
        $options = array(null => $this->txt("please_select")) + $this->getOptions($set);
        $si->setOptions($options);

        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_SET_DIMENSIONS));
        $this->ctrl->clearParameters($this);
        $this->toolbar->addInputItem($si);
        $this->toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_SET_DIMENSION);
        $this->toolbar->setCloseFormTag(true);
    }

    /**
     * @param Dimension[] $dimensions
     */
    protected function renderTable(array $dimensions)
    {
        $table = $this->getTMSTableGUI();

        $table->setData($dimensions);

        if (!$this->isSetInUse()) {
            $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));
        }

        $this->tpl->setContent($table->getHtml());
    }

    /**
     * @param ScaledFeedback\Config\Sets\Set $set
     * @return 	array <dim_id, title>
     */
    protected function getOptions(ScaledFeedback\Config\Sets\Set $set) : array
    {
        $result = array();
        $dimensions = $this->sortDimensions($this->db->selectAllDimensions());
        $set_dimensions = array_map(function (Dimension $a) {
            return $a->getDimId();
        }, $set->getDimensions());

        foreach ($dimensions as $dim) {
            if (!$dim->getIsLocked() && !in_array($dim->getDimId(), $set_dimensions)) {
                $result[$dim->getDimId()] = $dim->getTitle();
            }
        }

        return $result;
    }

    /**
     * @param 	Dimension[] $dimensions
     * @return 	Dimension[]
     */
    protected function sortDimensions(array $dimensions) : array
    {
        $i = 10;
        $res = array();
        uasort($dimensions, function (Dimension $a, Dimension $b) {
            if ($a->getOrdernumber() == $b->getOrdernumber()) {
                return 0;
            }
            if ($a->getOrdernumber() < $b->getOrdernumber()) {
                return -1;
            }
            return 1;
        });

        foreach ($dimensions as $dimension) {
            $dimension = $dimension->withOrdernumber($i);
            $res[] = $dimension;
            $i += 10;
        }

        return $res;
    }

    protected function getSet()
    {
        $id = $this->getValidatedIdFromGet();
        if ($id == -1) {
            return false;
        }
        return $this->db->selectSetById($id);
    }

    protected function getValidatedIdFromGet() : int
    {
        if (isset($_GET['id'])) {
            return (int) $_GET['id'];
        }
        return -1;
    }

    protected function isSetInUse() : bool
    {
        $set = $this->getSet();
        if (!$set) {
            return false;
        }
        return $set->getIsUsed();
    }

    protected function getSetTitle() : string
    {
        $set = $this->getSet();
        if (!$set) {
            return "";
        }
        return $set->getTitle();
    }

    protected function getTMSTableGUI() : ilTMSTableGUI
    {
        $table = parent::getTMSTableGUI();

        $table->setEnableTitle(true);
        $table->setTitle($this->txt("dimensions"));

        $table->setTopCommands(false);
        $table->setEnableHeader(true);
        $table->setRowTemplate("tpl.table_set_dimensions_row.html", $this->plugin_path);
        $this->ctrl->setParameter($this, "id", $this->getValidatedIdFromGet());
        $table->setFormAction($this->ctrl->getFormAction($this));
        $this->ctrl->clearParameters($this);
        $table->setExternalSorting(true);
        $table->setEnableAllCommand(true);
        $table->setShowRowsSelector(false);

        if (!$this->isSetInUse()) {
            $table->setSelectAllCheckbox(self::POST_VAR . "[]");
            $table->addCommandButton(self::CMD_SAVE_SET_DIMENSION, $this->txt("save"));
            $table->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        }

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("order"));
        $table->addColumn($this->txt("title"));

        return $table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, Dimension $dimension) {
            $tpl = $table->getTemplate();

            $ni = new ilNumberInputGUI("", "order[]");
            $ni->setSize("3");
            $ni->setDisabled($this->isSetInUse());
            $ni->setValue($dimension->getOrdernumber());

            if (!$this->isSetInUse()) {
                $tpl->setVariable("POST_VAR", self::POST_VAR);
                $tpl->setVariable("ID", $dimension->getDimId());
            }

            $tpl->setVariable("ORDER", $ni->render());
            $tpl->setVariable("TITLE", $dimension->getTitle());
            $tpl->setVariable("HIDDEN_ID_POST", "dim_ids");
            $tpl->setVariable("HIDDEN_ID", $dimension->getDimID());
        };
    }

    protected function tableCommand()
    {
        return self::CMD_SHOW_SET_DIMENSIONS;
    }

    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
