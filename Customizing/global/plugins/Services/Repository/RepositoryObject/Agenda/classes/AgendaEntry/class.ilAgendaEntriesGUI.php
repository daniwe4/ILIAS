<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\Agenda;
use CaT\Plugins\Agenda\Config\Blocks;
use CaT\Plugins\Agenda\AgendaEntry\DB;
use CaT\Plugins\Agenda\AgendaEntry\AgendaEntry;
use CaT\Plugins\Agenda\TableProcessing\TableProcessor;
use CaT\Plugins\AgendaItemPool\AgendaItem\DB as AIP_DB;
use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use CaT\Plugins\Agenda\EntryChecks\EntryChecks;
use ILIAS\TMS\AgendaItemInfo;

class ilAgendaEntriesGUI extends TMSTableParentGUI
{
    use ilHandlerObjectHelper;

    const CMD_SHOW_ENTRIES = "showEntries";
    const CMD_SAVE_ENTRIES = "saveEntries";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_DELETE_ENTRIES = "deleteEntries";
    const CMD_ADD_ENTRY = "addEntry";

    const F_DELETE_IDS = "delete_ids";
    const F_HIDDEN_ID = "hidden_id";
    const F_DELETE_ITEM = "delete_items";
    const F_POOL_ITEM = "pool_item";
    const F_DURATION = "duration";
    const F_IS_BLANK = "is_blank";
    const F_CONTENT = "content";
    const F_GOALS = "goals";

    const TABLE_ID = "agenda_entries";
    const MAX_POOL_ITEM_TITLE_LENGTH = 50;

    const NON_EDIT_KEY = -2;

    const MIN_DURATION_TIME = 0;
    const MAX_DURATION_TIME = 300;
    const DURATION_STEPS = 5;

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
     * @var ilAccess
     */
    protected $access;

    /**
     * @var CaT\Plugins\Agenda\AgendaEntry\ilDB
     */
    protected $db;

    /**
     * @var Agenda\TableProcessing\TableProcessor
     */
    protected $table_processor;

    /**
     * @var EntryChecks
     */
    protected $entry_checker;

    /**
     * @var ilObjAgenda
     */
    protected $object;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var int
     */
    protected $object_id;

    /**
     * @var int
     */
    protected $object_ref_id;

    /**
     * @var array
     */
    protected $agenda_item_infos;

    /**
     * @var int
     */
    protected $data_counter;

    /**
     * @var Blocks\DB
     */
    protected $blocks_db;

    /**
     * @var ILIAS\TMS\ReportUtilities\TreeObjectDiscovery
     */
    protected $tree_discovery;

    /**
     * @var bool
     */
    protected $edit_fixed_blocks;

    protected $show_no_edit_message;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilToolbarGUI $toolbar,
        ilAccess $access,
        DB $db,
        TableProcessor $table_processor,
        EntryChecks $entry_checker,
        ilObjAgenda $object,
        string $directory,
        Closure $txt,
        Blocks\DB $blocks_db,
        ILIAS\TMS\ReportUtilities\TreeObjectDiscovery $tree_discovery
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->toolbar = $toolbar;
        $this->access = $access;
        $this->db = $db;
        $this->table_processor = $table_processor;
        $this->entry_checker = $entry_checker;
        $this->object = $object;
        $this->directory = $directory;
        $this->txt = $txt;
        $this->blocks_db = $blocks_db;
        $this->tree_discovery = $tree_discovery;

        $this->object_id = (int) $object->getId();
        $this->object_ref_id = (int) $object->getRefId();

        $this->tpl->addOnLoadCode(
            "il.TMS.agenda.edu_active = " . $this->isEduTrackingActive() . ";"
            . "il.TMS.agenda.start_time = '" . $this->getStartTimeMinutes() . "';"
        );
    }

    protected function getStartTimeMinutes()
    {
        $h = $this->object->getSettings()->getStartTime()->format("H");
        $m = $this->object->getSettings()->getStartTime()->format("i");

        $minutes = 60 * (int) $h + (int) $m;

        return $minutes;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_ENTRIES:
                $this->showEntries();
                break;
            case self::CMD_SAVE_ENTRIES:
                $this->saveEntries();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE_ENTRIES:
                $this->deleteEntries();
                break;
            case self::CMD_ADD_ENTRY:
                $this->addEntry();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showEntries()
    {
        $entries = $this->db->selectFor($this->object_id);
        $processing = $this->createProcessingArray($entries);
        $this->showTable($processing);
    }

    protected function saveEntries()
    {
        $post = $_POST;
        $process = true;
        $message = [];

        $delete_ids = $post["delete_ids"];
        if (is_null($delete_ids)) {
            $delete_ids = array();
        }

        if (is_null($post["pool_item"]) || count($post["pool_item"]) == 0) {
            $this->showEntries();
            return;
        }

        $check_objects = $this->entry_checker->getCheckObjects(
            $post["pool_item"],
            $post["duration"],
            $delete_ids
        );

        if (!$this->entry_checker->checkMinimumAgendaDuration($check_objects)) {
            $message[] = sprintf(
                $this->txt("minimum_duration_not_reached"),
                EntryChecks::MIN_DURATION_TIME
            );
            $process = false;
        }

        if (!$this->entry_checker->checkPoolItemSelected($check_objects)) {
            $message[] = $this->txt("no_pool_item_selected");
            $process = false;
        }

        $processing = $this->createProcessingArrayFromPost($post);
        $processing = $this->updateProcessingForNonBlankNonEditable($processing);

        if ($process) {
            $processing = $this->setPositions($processing);
            $this->table_processor->process($processing, array(TableProcessor::ACTION_SAVE));
            $this->updateSession();

            if ($this->isEduTrackingActive()) {
                $this->updateEduTracking();
            }
            $this->ctrl->redirect($this, self::CMD_SHOW_ENTRIES);
        } else {
            ilUtil::sendFailure(join("<br>", $message));
        }

        $this->showTable($processing);
    }

    protected function updateProcessingForNonBlankNonEditable(array $processing) : array
    {
        $org_entries = $this->db->selectFor($this->object_id);
        $org_entries_ids = array_map(
            function ($entry) {
                return $entry->getId();
            },
            $org_entries
        );

        foreach ($processing as $key => $entry) {
            $obj = $entry["object"];

            if (!$obj->getPoolItemId()) {
                continue;
            }
            $aip_obj = $this->object->getAipById($obj->getPoolItemId());

            if (!$aip_obj->getIsBlank() && !$this->editFixedBlocks()) {
                $content = $aip_obj->getAgendaItemContent();
                $goals = $aip_obj->getGoals();

                $edited = array_search($obj->getId(), $org_entries_ids);
                $was_edited_before = $edited !== false;
                if ($was_edited_before) {
                    $edited_obj = $org_entries[$edited];

                    if ($edited_obj->getPoolItemId() === $aip_obj->getObjId()) {
                        $content = $edited_obj->getAgendaItemContent();
                        $goals = $edited_obj->getGoals();
                    }
                }

                $obj = $obj
                    ->withAgendaItemContent($content)
                    ->withGoals($goals);
                $processing[$key]["object"] = $obj;
            }
        }

        return $processing;
    }

    /**
     * Updates the session where this agenda is assigned to
     */
    protected function updateSession()
    {
        $this->object->updateSession();
    }

    /**
     * Updates edu trackings below parent
     *
     * @return void
     */
    protected function updateEduTracking()
    {
        $parent = $this->object->getParentCourse();
        if (is_null($parent)) {
            return;
        }

        foreach ($this->getAllChildrenOfByType((int) $parent->getRefId(), "xetr") as $xetr) {
            $xetr->updateIDDTime();
        }
    }

    /**
     * Adds a new entry to the processing array
     */
    protected function addEntry()
    {
        $post = $_POST;
        $post_entries = $this->createProcessingArrayFromPost($post);
        $post_entries = $this->updateProcessingForNonBlankNonEditable($post_entries);

        $new_entry = $this->db->getNewEntry($this->object_id);

        $id = 0;
        foreach ($post_entries as $entry) {
            if ($entry["object"]->getId() < $id) {
                $id = $entry["object"]->getId();
            }
        }

        $new_entry = $new_entry->withId($id - 1);

        $processing = array_merge(
            $post_entries,
            $this->createProcessingArray(array($new_entry))
        );

        $processing = $this->setPositions($processing);

        $this->showTable($processing);
    }

    protected function setPositions(array $processing) : array
    {
        $pos = 10;
        foreach ($processing as $key => $entry) {
            $obj = $entry["object"];
            $obj = $obj->withPosition($pos);
            $processing[$key]["object"] = $obj;
            $pos = $pos + 10;
        }

        return $processing;
    }

    protected function confirmDelete()
    {
        $post = $_POST;
        $processing = $this->createProcessingArrayFromPost($post);
        $processing = $this->updateProcessingForNonBlankNonEditable($processing);
        if (
        !$this->access->checkAccess(
            "edit_agenda_entries",
            "",
            $this->object_ref_id
        )
        ) {
            $processing = $this->updateProcessingObjectWithDBValues($processing);
        }

        $delete_records = array_filter($processing, function ($record) {
            if ($record[TableProcessor::KEY_DELETE]) {
                return $record;
            }
        });

        $cnt_delete_records = count($delete_records);

        if ($cnt_delete_records > 0) {
            if ($cnt_delete_records > 1) {
                $confirmation = $this->getConfirmationForm($this->txt("confirm_delete_multi"));
            } else {
                $confirmation = $this->getConfirmationForm($this->txt("confirm_delete_single"));
            }


            foreach ($delete_records as $record) {
                $object = $record[TableProcessor::KEY_OBJECT];
                $value = $this->txt("remove_unsaved");
                if (!is_null($object->getPoolItemId())) {
                    $value = $this->getPoolItemTitle($object->getPoolItemId())
                        . " "
                        . $this->txt("timings")
                        . " "
                        . $object->getDuration()
                        . " "
                        . $this->txt("timepiece");
                }
                $confirmation->addItem(self::F_DELETE_ITEM . "[]", "", $value);
            }

            $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_ENTRIES);
            $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_ENTRIES);

            $confirmation->addHiddenItem(TableProcessor::KEY_OBJECT, base64_encode(serialize($processing)));
            $this->tpl->setContent($confirmation->getHTML());
        } else {
            \ilUtil::sendInfo($this->txt("delete_no_items_selected"), true);
            $this->showTable($processing);
        }
    }

    protected function deleteEntries()
    {
        $post = $_POST;
        $options = unserialize(base64_decode($post[TableProcessor::KEY_OBJECT]));
        $processed_options = $this->table_processor->process($options, array(TableProcessor::ACTION_DELETE));

        if (count($options) > count($processed_options)) {
            if (count($options) - count($processed_options) == 1) {
                ilUtil::sendInfo($this->txt("delete_single_successfull"));
            } else {
                ilUtil::sendInfo($this->txt("delete_successfull"));
            }

            $this->updateSession();

            if ($this->isEduTrackingActive()) {
                $this->updateEduTracking();
            }
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_ENTRIES);
    }

    protected function showTable(array $processing)
    {
        $delete = $this->access->checkAccess(
            "delete_agenda_entries",
            "",
            $this->object_ref_id
        );

        $edit = $this->access->checkAccess(
            "edit_agenda_entries",
            "",
            $this->object_ref_id
        );

        if ($edit) {
            $this->setToolbar();
        }

        $css_file = sprintf(
            '%s/templates/css/Table.css',
            $this->directory
        );
        $this->tpl->addCss($css_file);

        $js_file = sprintf(
            '%s/templates/js/onItemSelection.js',
            $this->directory
        );
        $this->tpl->addJavaScript($js_file);

        $this->addItemsToLookup();

        $this->addProcessingToLookup($processing);

        $this->tpl->addOnLoadCode(
            '$(document).ready(function() {il.TMS.agenda.itemselection.registerTimeInputEvents();il.TMS.agenda.itemselection.init();});'
        );

        $table = $this->getTMSTableGUI();
        $table = $this->configureTable($table);
        $table->setTitle($this->txt("agenda_entries"));
        $table->setLimit(10000);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setData($processing);

        if ($edit) {
            $table->addCommandButton(self::CMD_SAVE_ENTRIES, $this->txt("save"));
        }
        if ($delete) {
            $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));
        }
        $table->setMaxCount(count($processing));

        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        //Init Component
        $legacy = $f->legacy("<span>" . $this->txt("total_idd_time") . " </span><span id='total_idd_time'></span>");

        $this->tpl->setContent($renderer->render($legacy) . $table->getHtml());
    }

    protected function addItemsToLookup()
    {
        $agenda_items = $this->getAgendaItemInfos();

        //add options to js-lookup:
        foreach ($agenda_items as $item) {
            $item_id = $item->getObjId();
            $item_relevant = $this->txt('relevance_no');
            if ($item->getIddRelevant()) {
                $item_relevant = $this->txt('relevance_yes');
            }

            $data = [
                'contents' => $item->getAgendaItemContent(),
                'goals' => $item->getGoals(),
                'idd_relevance' => $item_relevant,
                'calc_idd_time' => (int) $item->getIddRelevant(),
                'is_blank' => (int) $item->getIsBlank(),
                'edit_fixed' => (int) $this->editFixedBlocks(),
                'duration' => 0,
                'position' => 0
            ];

            $js = "il.TMS.agenda.itemselection.addToLookup(" . $item_id . ', ' . json_encode($data) . ");";
            $this->tpl->addOnLoadCode($js);
        }
    }

    protected function addProcessingToLookup(array $processing)
    {
        foreach ($processing as $record) {
            $item = $record["object"];
            $item_id = $item->getId();

            $data = [
                'key' => (string) $item->getPoolItemId(),
                'contents' => $item->getAgendaItemContent() ?? "",
                'goals' => $item->getGoals() ?? "",
                'calc_idd_time' => (int) $item->getIddTime(),
                'duration' => $item->getDuration() ?? "00"
            ];

            $js = "il.TMS.agenda.itemselection.addCurrentToLookup(" . $item_id . ', ' . json_encode($data) . ");";
            $this->tpl->addOnLoadCode($js);
        }
    }

    public function getTable()
    {
        return $this->getTMSTableGUI();
    }

    /**
     * @param AgendaEntry[] $entries
     * @return array mixed
     */
    protected function createProcessingArray(array $entries) : array
    {
        $ret = array();

        foreach ($entries as $entry) {
            $ret[] = array(
                TableProcessor::KEY_OBJECT => $entry,
                TableProcessor::KEY_DELETE => false,
                TableProcessor::KEY_ERROR => array(),
                TableProcessor::KEY_MESSAGE => array()
            );
        }

        return $ret;
    }

    protected function createProcessingArrayFromPost(array $post) : array
    {
        $ret = array();
        $del_array = array();

        if ($post[self::F_DELETE_IDS] && count($post[self::F_DELETE_IDS]) > 0) {
            $del_array = $post[self::F_DELETE_IDS];
        }

        foreach ($this->getObjectsFromPost($post) as $key => $entry) {
            $ret[$key] = array(
                TableProcessor::KEY_OBJECT => $entry,
                TableProcessor::KEY_ERROR => array()
            );

            $ret[$key][TableProcessor::KEY_DELETE] = in_array($entry->getId(), $del_array) && array_key_exists($key, $del_array);
        }

        return $ret;
    }

    /**
     * @return Entries[] | []
     */
    protected function getObjectsFromPost(array $post) : array
    {
        $ret = array();

        if ($post[self::F_POOL_ITEM] && count($post[self::F_POOL_ITEM]) > 0) {
            foreach ($post[self::F_POOL_ITEM] as $key => $pool_item_id) {
                if (!is_null($pool_item_id) && $pool_item_id != "") {
                    $pool_item_id = (int) $pool_item_id;
                } else {
                    $pool_item_id = null;
                }

                $is_blank = (bool) $post[self::F_IS_BLANK][$key];

                $content = null;
                if (
                    array_key_exists(self::F_CONTENT, $post) &&
                    array_key_exists($key, $post[self::F_CONTENT]) &&
                    trim($post[self::F_CONTENT][$key]) != ""
                ) {
                    $content = trim($post[self::F_CONTENT][$key]);
                }

                $goals = null;
                if (
                    array_key_exists(self::F_GOALS, $post) &&
                    array_key_exists($key, $post[self::F_GOALS]) &&
                    trim($post[self::F_GOALS][$key]) != ""
                ) {
                    $goals = trim($post[self::F_GOALS][$key]);
                }

                $ret[$key] = new Agenda\AgendaEntry\AgendaEntry(
                    (int) $post[self::F_HIDDEN_ID][$key],
                    $this->object_id,
                    $pool_item_id,
                    (int) $post[self::F_DURATION][$key],
                    (int) $post["position"][$key],
                    (float) $post["idd_time"][$key],
                    $is_blank,
                    $content,
                    $goals
                );
            }
        }

        uasort($ret, array($this, 'sortByPosition'));

        return $ret;
    }

    protected function fillUp(string $value)
    {
        return str_pad($value, 2, "0", STR_PAD_LEFT);
    }

    protected function getConfirmationForm(string $header_text) : ilConfirmationGUI
    {
        require_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_ENTRIES));
        $confirmation->setHeaderText($header_text);

        return $confirmation;
    }

    protected function getPoolItemTitle(int $pool_item_id) : string
    {
        $infos = $this->getAgendaItemInfos();
        foreach ($infos as $info) {
            if ($info->getObjId() == $pool_item_id) {
                return $info->getTitle();
            }
        }

        return "";
    }

    /**
     * Get all infos via ente
     *
     * @return AgendaItemInfo[]
     */
    protected function getAgendaItemInfos() : array
    {
        if ($this->agenda_item_infos === null) {
            $return = [];

            if (ilPluginAdmin::isPluginActive('xaip') === true) {
                $agenda_pools = $this->getAllReadableAgendaItemPools();

                $agenda_items = array_map(
                    function ($agenda_pool) {
                        $items = $agenda_pool->getObjectActions()->getAllAgendaItemsByPoolId((int) $agenda_pool->getId());
                        return array_filter($items, function ($i) {
                            return $i->getIsActive();
                        });
                    },
                    $agenda_pools
                );

                array_walk_recursive($agenda_items, function ($a) use (&$return) {
                    $return[] = $a;
                });
            }

            $this->agenda_item_infos = $return;
        }

        return $this->agenda_item_infos;
    }

    /**
     * Get all agenda item pools in the installation.
     *
     * @return \ilObjAgendaItemPool[]
     */
    protected function getAllReadableAgendaItemPools()
    {
        $children_ref_ids = array_keys($this->tree_discovery->getAllChildrenNodeIdsByTypeOfObject(1, "xaip"));
        return array_map(
            function ($child_ref) {
                return ilObjectFactory::getInstanceByRefId($child_ref);
            },
            array_filter(
                $children_ref_ids,
                function ($child_ref) {
                    return $this->access->checkAccess("read", "", $child_ref)
                        && $this->access->checkAccess("use_agenda_item", "", $child_ref);
                }
            )
        );
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return array 	of search type
     */
    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        $children_ref_ids = array_keys($this->tree_discovery->getAllChildrenNodeIdsByTypeOfObject($ref_id, $search_type));
        return array_map(
            function ($child_ref) {
                return ilObjectFactory::getInstanceByRefId($child_ref);
            },
            $children_ref_ids
        );
    }

    protected function noParentInfo()
    {
        ilUtil::sendInfo($this->txt("no_parent_session"));
    }

    protected function updateProcessingObjectWithDBValues(array $processing) : array
    {
        foreach ($processing as &$container) {
            $container[TableProcessor::KEY_OBJECT] =
                $this->actions->selectForId($container[TableProcessor::KEY_OBJECT]->getId());
        }

        return $processing;
    }

    protected function getDIC() : ILIAS\DI\Container
    {
        return $GLOBALS["DIC"];
    }

    protected function getEntityRefId() : int
    {
        return ROOT_FOLDER_ID;
    }

    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this, self::CMD_SHOW_ENTRIES));
        $this->toolbar->setCloseFormTag(false);
        $this->toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_ENTRY);
    }

    public function isEduTrackingActive() : bool
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        return ilPluginAdmin::isPluginActive("xetr");
    }

    protected function sortByPosition(AgendaEntry $a, AgendaEntry $b) : int
    {
        if ($a->getPosition() > $b->getPosition()) {
            return 1;
        }

        if ($a->getPosition() < $b->getPosition()) {
            return -1;
        }

        return 0;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    protected function renderTable(array $data)
    {
        $table = $this->getTMSTableGUI();
        $this->configureTable($table);
        $table->setData($data);
        $this->tpl->setContent(
            $table->getHtml()
        );
    }

    public function configureTable(ilTMSTableGUI $table)
    {
        $table->setEnableTitle(true);
        $table->setShowRowsSelector(false);
        $table->setTopCommands(true);
        $table->setEnableHeader(true);
        $table->setExternalSorting(true);
        $table->setExternalSegmentation(true);
        $table->setRowTemplate("tpl.entry_row.html", $this->object->getDirectory());

        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("position"));
        $table->addColumn($this->txt("pool_item"));
        $table->addColumn($this->txt("pool_item_contents"));
        $table->addColumn($this->txt("pool_item_goals"));
        $table->addColumn($this->txt("duration"));
        $table->addColumn($this->txt("start_time"));
        $table->addColumn($this->txt("end_time"));

        if ($this->isEduTrackingActive()) {
            $table->addColumn($this->txt("pool_item_idd_relevance"));
        }

        if ($this->isEduTrackingActive()) {
            $table->addColumn($this->txt("idd_time"));
        }

        $this->data_counter = 0;

        return $table;
    }

    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $data) {
            $tpl = $table->getTemplate();

            /** @var AgendaEntry $object */
            $object = $data[TableProcessor::KEY_OBJECT];
            $errors = $data[TableProcessor::KEY_ERROR];
            $message = $data[TableProcessor::KEY_MESSAGE];

            $edit = $this->access->checkAccess(
                "edit_agenda_entries",
                "",
                $this->object_ref_id
            );

            $delete = $this->access->checkAccess(
                "delete_agenda_entries",
                "",
                $this->object_ref_id
            );
            $blank = $object->getIsBlank();
            $new_item = $object->getId() < 0;

            if ($delete) {
                $tpl->setVariable("ID", $object->getId());
                $tpl->setVariable("POST_VAR", self::F_DELETE_IDS);
            }

            $tpl->setVariable("HIDDEN_ID", $object->getId());
            $tpl->setVariable("HIDDEN_ID_POST_VAR", self::F_HIDDEN_ID);
            $tpl->setVariable("DELETE_COUNTER", $this->data_counter);
            $tpl->setVariable("HIDDEN_IS_BLANK_POST_VAR", self::F_IS_BLANK . "[" . $this->data_counter . "]");
            $tpl->setVariable("HIDDEN_IS_BLANK", $blank);
            $tpl->setVariable("DAY_START_TIME", $this->getStartTimeMinutes());

            $ni = new ilNumberInputGUI("", "position[" . $this->data_counter . "]");
            $ni->setSize("3");
            $ni->setValue($object->getPosition());
            $tpl->setVariable("POSITION", $ni->render());

            $options = $this->getAgendaItemOptions();
            $selected = $object->getPoolItemId();
            if (!$this->keyExists($selected, $options)) {
                $this->showNoEditMessage();
                $options = array_merge($options, $this->getNonEditArray());
                $selected = self::NON_EDIT_KEY;
            }
            $test = new ilGroupableSelectInputGUI("", self::F_POOL_ITEM . "[" . $this->data_counter . "]");
            $test->setGroups($options);
            $test->setDisabled(!$edit);
            $test->setValue($selected);
            $test->setTextLength(self::MAX_POOL_ITEM_TITLE_LENGTH);

            $item_contents = $object->getAgendaItemContent();
            $item_goals = $object->getGoals();
            $item_relevant = '';
            $idd_learning_time = "00:00";

            if (!is_null($object->getPoolItemId())) {
                $item = $this->getItemById($object->getPoolItemId());
                if (!is_null($item)) {
                    if ($this->isEduTrackingActive()) {
                        $item_relevant = $this->txt('relevance_no');
                        if ($item->getIDDRelevant()) {
                            $item_relevant = $this->txt('relevance_yes');
                            $minutes = $object->getIDDTime();
                            $hh = floor($minutes / 60);
                            $mm = $minutes - ($hh * 60);
                            $idd_learning_time =
                                str_pad((string) $hh, 2, "0", STR_PAD_LEFT)
                                . ":"
                                . str_pad((string) $mm, 2, "0", STR_PAD_LEFT)
                            ;
                        }
                    }

                    if (!$this->editFixedBlocks()) {
                        $item_contents = $item_contents ?? $item->getAgendaItemContent();
                        $item_goals = $item_goals ?? $item->getGoals();
                    }
                }
            }

            $test->addCustomAttribute('onchange="il.TMS.agenda.itemselection.onItemSelect(this, this.value);"');
            $tpl->setVariable("POOL_ITEMS", $test->render());

            if (array_key_exists(self::F_POOL_ITEM, $errors)) {
                $this->fillErrorMessages("pool_item", $errors[self::F_POOL_ITEM]);
            }

            $this->fillContentColumn($tpl, $edit, $blank, $item_contents, $new_item);
            $this->fillGoalsColumn($tpl, $edit, $blank, $item_goals, $new_item);

            $duration = "00";
            if (!is_null($object->getDuration())) {
                $duration = $object->getDuration();
            }
            $si = new ilSelectInputGUI("", self::F_DURATION . "[" . $this->data_counter . "]");
            $si->setOptions($this->getDurationOptions());
            $si->setValue($duration);
            $si->setDisabled(!$edit);
            $tpl->setVariable("DURATION", $si->render());

            if ($this->isEduTrackingActive()) {
                $tpl->setCurrentBlock("idd_relevant");
                $tpl->setVariable("POOL_ITEM_IDD_RELEVANCE", $item_relevant);
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock("idd_time");
                $tpl->setVariable("IDD_TIME", $idd_learning_time);
                $tpl->parseCurrentBlock();
            }

            if ($message && count($message) > 0) {
                $message = array_map(function ($mes) {
                    return $this->txt($mes);
                }, $message);
                $tpl->setCurrentBlock("message");
                $tpl->setVariable("MESSAGE_CSS_ROW", $this->css_row);
                $tpl->setVariable("MESSAGE", implode("<br />", $message));
                $tpl->parseCurrentBlock();
            }

            $this->data_counter++;
        };
    }

    protected function getDurationOptions()
    {
        $options = [];
        for ($i = self::MIN_DURATION_TIME; $i <= self::MAX_DURATION_TIME; $i = $i + self::DURATION_STEPS) {
            $options[$i] = $i;
        }

        return $options;
    }

    protected function fillContentColumn(
        ilTemplate $tpl,
        bool $edit,
        bool $blank,
        $item_contents,
        bool $new_item
    ) {
        if (
            $new_item ||
            (!$blank && !$this->editFixedBlocks())
        ) {
            $tpl->setVariable("POOL_ITEM_CONTENTS", $item_contents);

            $tpl->setVariable("C_HIDE_FIXED_EDIT", "hide");
            $tpl->setVariable("C_HIDE_BLANK", "hide");
        }

        if (!$new_item && $blank) {
            $tpl->setVariable("I_CONTENT_BLANK", $item_contents);
            if (!$edit) {
                $tpl->setVariable("CB_DISABLED", "disabled");
            }

            $tpl->setVariable("C_HIDE_BLANK", "");
            $tpl->setVariable("C_HIDE_NON_EDIT", "hide");
            $tpl->setVariable("C_HIDE_FIXED_EDIT", "hide");
            $tpl->setVariable("CEF_DISABLED", "disabled");
        }

        if (!$new_item && !$blank && $this->editFixedBlocks()) {
            $tpl->setVariable("I_CONTENT_EDIT_FIXED", $item_contents);
            if (!$edit) {
                $tpl->setVariable("CEF_DISABLED", "disabled");
            }

            $tpl->setVariable("C_HIDE_FIXED_EDIT", "");
            $tpl->setVariable("C_HIDE_NON_EDIT", "hide");
            $tpl->setVariable("C_HIDE_BLANK", "hide");
            $tpl->setVariable("CB_DISABLED", "disabled");
        }

        $tpl->setVariable("I_CONTENT_EDIT_FIXED_POST", self::F_CONTENT . "[" . $this->data_counter . "]");
        $tpl->setVariable("I_CONTENT_BLANK_POST", self::F_CONTENT . "[" . $this->data_counter . "]");
    }

    protected function fillGoalsColumn(
        ilTemplate $tpl,
        bool $edit,
        bool $blank,
        $item_goals,
        bool $new_item
    ) {
        if (
            $new_item ||
            (!$blank && !$this->editFixedBlocks())
        ) {
            $tpl->setVariable("POOL_ITEM_GOALS", $item_goals);

            $tpl->setVariable("G_HIDE_FIXED_EDIT", "hide");
            $tpl->setVariable("G_HIDE_BLANK", "hide");
        }

        if (!$new_item && $blank) {
            $tpl->setVariable("I_GOALS_BLANK", $item_goals);
            if (!$edit) {
                $tpl->setVariable("GB_DISABLED", "disabled");
            }

            $tpl->setVariable("G_HIDE_BLANK", "");
            $tpl->setVariable("G_HIDE_NON_EDIT", "hide");
            $tpl->setVariable("G_HIDE_FIXED_EDIT", "hide");
            $tpl->setVariable("GEF_DISABLED", "disabled");
        }

        if (!$new_item && !$blank && $this->editFixedBlocks()) {
            $tpl->setVariable("I_GOALS_EDIT_FIXED", $item_goals);
            if (!$edit) {
                $tpl->setVariable("CEF_DISABLED", "disabled");
            }
            $tpl->setVariable("G_HIDE_FIXED_EDIT", "");
            $tpl->setVariable("G_HIDE_NON_EDIT", "hide");
            $tpl->setVariable("G_HIDE_BLANK", "hide");
            $tpl->setVariable("GB_DISABLED", "disabled");
        }

        $tpl->setVariable("I_GOALS_BLANK_POST", self::F_GOALS . "[" . $this->data_counter . "]");
        $tpl->setVariable("I_GOALS_EDIT_FIXED_POST", self::F_GOALS . "[" . $this->data_counter . "]");
    }

    /**
     * @param string 	$block
     * @param string[] 	$messages
     */
    protected function fillErrorMessages(string $block, array $messages)
    {
        $errors = array_map(function ($err) {
            return $this->txt($err);
        }, $messages);
        $this->tpl->setCurrentBlock($block . "_alert");
        $this->tpl->setVariable("IMG_ALERT", ilUtil::getImagePath("icon_alert.svg"));
        $this->tpl->setVariable("ALT_ALERT", $this->txt("alert"));
        $this->tpl->setVariable("TXT_ALERT", implode(",", $errors));
        $this->tpl->parseCurrentBlock();
    }

    /**
     * Get information for agenda items
     *
     * @return array<string, array(string | int, string)>
     */
    protected function getAgendaItemOptions()
    {
        $infos = $this->getAgendaItemInfos();

        $ret = array();
        $ret[""] = array(null => $this->txt("please_select"));

        if (ilPluginAdmin::isPluginActive('xaip') === true) {
            foreach ($infos as $info) {
                $pool_title = ilObject::_lookupTitle($info->getPoolId());
                $ret[$pool_title][$info->getObjId()] = $info->getTitle();
            }
        }

        ksort($ret);

        foreach ($ret as $group => &$items) {
            uasort($items, function (string $a, string $b) {
                return strcasecmp($a, $b);
            });
        }

        return $ret;
    }

    /**
     * Get additional information about the agenda-item
     */
    public function getItemInfo(int $item_id) : string
    {
        $buffer = array();
        $item = $this->getItemById($item_id);
        if (!is_null($item)) {
            $buffer[] = $item->getContents();
            $buffer[] = $item->getIDDRelevant();
            $buffer[] = $item->getGoals();
        }
        return implode('<br>', $buffer);
    }

    /**
     * Get agenda_item by id.
     *
     * @param 	int 	$item_id
     * @return 	AgendaEntry | null
     */
    protected function getItemById(int $item_id)
    {
        foreach ($this->getAgendaItemInfos() as $item) {
            if ($item->getObjId() === $item_id) {
                return $item;
            }
        }
        return null;
    }

    protected function editFixedBlocks() : bool
    {
        if (is_null($this->edit_fixed_blocks)) {
            $this->edit_fixed_blocks = $this->blocks_db->selectBlockConfig()->isEditFixedBlocks();
        }
        return $this->edit_fixed_blocks;
    }

    protected function tableCommand()
    {
        self::CMD_SHOW_ENTRIES;
    }

    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function showNoEditMessage()
    {
        if (!$this->show_no_edit_message) {
            ilUtil::sendInfo($this->txt("some_blocks_not_visible"));
            $this->show_no_edit_message = true;
        }
    }

    protected function keyExists($haystack, array $search) : bool
    {
        foreach ($search as $s) {
            if (array_key_exists($haystack, $s)) {
                return true;
            }
        }

        return false;
    }

    protected function getNonEditArray() : array
    {
        return [
            $this->txt("no_edit_grp_title") => [
                self::NON_EDIT_KEY => $this->txt("no_edit")
            ]
        ];
    }
}
