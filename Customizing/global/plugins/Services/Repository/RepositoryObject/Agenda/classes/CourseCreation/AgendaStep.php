<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Agenda\CourseCreation;

use ILIAS\TMS\CourseCreation\RequestBuilder;
use CaT\Ente\ILIAS\Entity;
use CaT\Plugins\Agenda\AgendaEntry\AgendaEntry;
use CaT\Plugins\Agenda\EntryChecks\EntryChecks;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use ILIAS\TMS\Wizard\Player;
use ilUtil;

/**
 * Step to show configuration table for the agenda
 */
class AgendaStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_URL = "f_url";
    const F_PASSWORD = "f_password";
    const F_TUTOR_LOGIN = "f_tutor_login";
    const F_TUTOR_PASSWORD = "tutor_password";
    const F_REQUIRED_MINUTES = "f_required_minutes";
    const F_OBJECT_REF_ID = "ref_id";

    const VC_TYPE = "Generic";

    const DATA_PREFIX = "agenda_";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var \ilObjAgenda
     */
    protected $owner;

    /**
     * @var int
     */
    protected $prio_stepper;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilTemplate
     */
    protected $tpl;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var bool
     */
    protected $data_appended;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var ILIAS\TMS\ReportUtilities\TreeObjectDiscovery
     */
    protected $tree_discovery;

    public function __construct(Entity $entity, \ilObjAgenda $owner, int $prio_stepper)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->prio_stepper = $prio_stepper;
        $this->txt = $owner->txtClosure();
        $this->tpl = $this->getDIC()->ui()->mainTemplate();
        $this->access = $this->getDIC()->access();
        $this->data_appended = false;
        $this->tree_discovery = new \ilTreeObjectDiscovery($this->getDIC()->repositoryTree());

        $this->addJavaScriptFiles();
        $this->addCSSFiles();
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("agenda");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("agenda_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $form->setId("agenda_step");

        $this->addIddTotalCounter($form);
        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->owner->getTitle());
        $form->addItem($sec);

        $this->addNewRowButton($form);
        $this->addAgendaTable($form);
        $this->addDeleteButton($form);

        $this->tpl->addOnLoadCode("il.TMS.agenda.edu_active = " . $this->owner->isEduTrackingActive());
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if ($this->data_appended) {
            return;
        }

        if (count($data) > 0) {
            if (array_key_exists($this->owner->getRefId(), $data)) {
                $start_time = $data[$this->owner->getRefId()]["start_time"];

                $this->tpl->addOnLoadCode(
                    "il.TMS.agenda.start_time = " . $start_time . ";"
                );
                $this->tpl->addOnLoadCode("il.TMS.agenda.addNewRow.updateStartTime()");
            }

            $ref = self::DATA_PREFIX . $this->owner->getRefId();
            if (
                array_key_exists($ref, $data) &&
                count($data[$ref]) > 0
            ) {
                foreach ($data[$ref] as $key => $value) {
                    $data = $value;
                    $data['edit_fixed'] = (int) $this->owner->editFixedBlocks();
                    $data['is_blank'] = (int) $data['is_blank'];

                    $js = "il.TMS.agenda.addNewRow.addData("
                        . $key
                        . ', '
                        . json_encode($data)
                        . ");";
                    $this->tpl->addOnLoadCode($js);
                }
            }
            $this->tpl->addOnLoadCode("il.TMS.agenda.addNewRow.updateTable()");
            $this->data_appended = true;
        }
        $this->tpl->addOnLoadCode("il.TMS.agenda.itemselection.init()");
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $actions = null;
        if (\ilPluginAdmin::isPluginActive('xaip') === true) {
            $obj = \ilPluginAdmin::getPluginObjectById("xaip");
            $actions = $obj->getActions();
        }

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->owner->getTitle());
        $form->addItem($sec);

        $entry_checker = new EntryChecks();
        $topics = [];
        $total_idd_time = 0;
        $total_duration = 0;
        $tpl = new \ilTemplate("tpl.ul.html", true, true, $this->owner->getDirectory());
        foreach ($data[self::DATA_PREFIX . $this->owner->getRefId()] as $ai_entry) {
            $relevant = false;
            $title = "";
            if (!is_null($actions)) {
                $item = $actions->getAgendaItemById((int) $ai_entry["pool_item"]);
                $relevant = $item->getIddRelevant();
                $title = $item->getTitle();
                $training_topics = $item->getTrainingTopics();
                if (is_array($training_topics)) {
                    $topics = array_merge($topics, $training_topics);
                }
            }

            if ($relevant) {
                $total_idd_time += $ai_entry["duration"];
            }

            $total_duration += $ai_entry["duration"];

            $tpl->setCurrentBlock("entry");
            $tpl->setVariable("VALUE", $title);
            $tpl->parseCurrentBlock("entry");
        }

        $topics = array_unique($topics);
        $tpl2 = new \ilTemplate("tpl.ul.html", true, true, $this->owner->getDirectory());
        $cc_actions = $this->owner->getCourseClassificationActions();

        if (!is_null($cc_actions)) {
            $topc_names = $cc_actions->getTopicNames($topics);
            foreach ($topc_names as $value) {
                $tpl2->setCurrentBlock("entry");
                $tpl2->setVariable("VALUE", $value);
                $tpl2->parseCurrentBlock("entry");
            }
        }

        $start_time = $data[$this->owner->getRefId()]["start_time"];
        $h_start = floor($start_time / 60);
        $m_start = $start_time % 60;

        $end_time = $start_time + $total_duration;
        $h_end = floor($end_time / 60);
        $m_end = $end_time % 60;

        $schedule = $this->padLeft((string) $h_start);
        $schedule .= ":";
        $schedule .= $this->padLeft((string) $m_start);
        $schedule .= " ";
        $schedule .= $this->txt("oclock");
        $schedule .= " - ";
        $schedule .= $this->padLeft((string) $h_end);
        $schedule .= ":";
        $schedule .= $this->padLeft((string) $m_end);
        $schedule .= " ";
        $schedule .= $this->txt("oclock");

        $hours = floor($total_idd_time / 60);
        $minutes = $total_idd_time - ($hours * 60);
        $idd_time =
            $this->padLeft((string) $hours)
            . ":"
            . $this->padLeft((string) $minutes)
            . " "
            . $this->txt("hours")
        ;

        $item = new \ilNonEditableValueGUI($this->txt("pool_items"), "", true);
        $item->setValue($tpl->get());
        $form->addItem($item);

        $item = new \ilNonEditableValueGUI($this->txt("topics"), "", true);
        $item->setValue($tpl2->get());
        $form->addItem($item);

        $item = new \ilNonEditableValueGUI($this->txt("schedule"), "", true);
        $item->setValue($schedule);
        $form->addItem($item);

        $item = new \ilNonEditableValueGUI($this->txt("idd_learning_time"), "", true);
        $item->setValue($idd_time);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $ref = self::DATA_PREFIX . $this->owner->getRefId();
        foreach ($data[$ref] as $key => $value) {
            $data[$ref][$key]["content"] = $value["content"];
            $data[$ref][$key]["goals"] = $value["goals"];
        }

        $this->request_builder->addConfigurationFor(
            $this->owner,
            ["entries" => $data[$ref]]
        );
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $post = $_POST;
        $process = true;
        $message = [$this->txt("error_header")];
        $entry_checker = new EntryChecks();
        $delete_ids = $post["delete_ids"];
        $is_previous = array_key_exists("previous", $post["cmd"]);
        $is_next = array_key_exists("next", $post["cmd"]);
        $start_time = (int) $post["day_start_time"];

        if (is_null($delete_ids) || $is_next || $is_previous) {
            $delete_ids = array();
        }

        if (!array_key_exists('duration', $post)) { //when user hits F5 before saving, e.g.
            return null;
        }

        $check_objects = $entry_checker->getCheckObjects(
            $post["pool_item"],
            $post["duration"],
            $delete_ids
        );

        $exec_delete = $post["cmd"]["save_step"] === $this->txt("delete");
        $process = !$exec_delete;
        if ($exec_delete && count($delete_ids) === 0) {
            $message[] = $this->txt("no_pool_item_selected");
            $process = false;
        }

        if (!$entry_checker->checkMinimumAmount($check_objects)) {
            $message[] = sprintf(
                $this->txt("minimum_entries_not_reached"),
                EntryChecks::MIN_AMOUNT_ENTRIES
            );
            $process = false;
        }

        if (!$entry_checker->checkMinimumAgendaDuration($check_objects)) {
            $message[] = sprintf(
                $this->txt("minimum_duration_not_reached"),
                EntryChecks::MIN_DURATION_TIME
            );
            $process = false;
        }

        if (!$entry_checker->checkPoolItemSelected($check_objects)) {
            $message[] = $this->txt("no_pool_item_selected");
            $process = false;
        }

        if (!$entry_checker->checkMaxDuration($check_objects)) {
            $message[] = $this->txt("duration_exceds_24h");
            $process = false;
        }

        if (!$entry_checker->checkMaxDuration($check_objects, $start_time)) {
            $message[] = $this->txt("agenda_exceds_day");
            $process = false;
        }

        $data = [];

        unset($post["cmd"]);
        unset($post["btn"]);
        unset($post["table"]);

        foreach ($post["pool_item"] as $key => $value) {
            if (!$is_next && !$is_previous && (int) $post["delete_ids"][$key] === -1) {
                continue;
            }

            if (
                $exec_delete &&
                array_key_exists($key, $post["delete_ids"]) &&
                (int) $post["hidden_id"][$key] == (int) $post["delete_ids"][$key]
            ) {
                continue;
            }

            $content = '';
            if (
                array_key_exists("content", $post) &&
                array_key_exists($key, $post["content"])
            ) {
                $content = trim($post["content"][$key]);
            }

            $goals = '';
            if (
                array_key_exists("goals", $post) &&
                array_key_exists($key, $post["goals"])
            ) {
                $goals = trim($post["goals"][$key]);
            }

            $duration = 0;
            if (
                array_key_exists("duration", $post) &&
                array_key_exists($key, $post["duration"])
            ) {
                $duration = (int) $post["duration"][$key];
            }

            $data[$key] = [
                "pool_item" => (int) $value,
                "duration" => $duration,
                "position" => (int) $post["position"][$key],
                "is_blank" => $post["is_blank"][$key],
                "content" => $content,
                "goals" => $goals
            ];
        }

        usort($data, array($this, 'sortByPosition'));
        $data = $this->setPositions($data);

        if (!$process) {
            if (count($message) > 1) {
                ilUtil::sendFailure(join("<br>", $message));
            }
            if (count($data) == 0) {
                $data[0] = [
                    "pool_item" => null,
                    "duration" => 0,
                    "position" => 10,
                    "is_blank" => 0,
                    "content" => "",
                    "goals" => ""
                ];
            }

            $this->addDataToForm(
                $form,
                [
                    $this->owner->getRefId() => ["start_time" => $start_time],
                    self::DATA_PREFIX . $this->owner->getRefId() => $data
                ]
            )
            ;
            return null;
        }

        return [self::DATA_PREFIX . $this->owner->getRefId() => $data];
    }

    protected function setPositions(array $data) : array
    {
        $pos = 10;
        foreach ($data as $key => $entry) {
            $entry["position"] = $pos;
            $data[$key] = $entry;
            $pos = $pos + 10;
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function needPreviousStepData() : bool
    {
        return true;
    }

    protected function sortByPosition(array $a, array $b) : int
    {
        if ($a["position"] > $b["position"]) {
            return 1;
        }

        if ($a["position"] < $b["position"]) {
            return -1;
        }

        return 0;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return (300 + $this->prio_stepper);
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId() : int
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    protected function txt(string $id) : string
    {
        return call_user_func($this->txt, $id);
    }

    /**
     * Adds required java script files
     */
    protected function addJavaScriptFiles()
    {
        $path = '%s/templates/js/%s';
        $plugin_directory = $this->owner->getDirectory();
        $files = array("onItemSelection.js", "addRow.js");

        foreach ($files as $file) {
            $js_file = sprintf(
                $path,
                $plugin_directory,
                $file
            );

            $this->tpl->addJavaScript($js_file);
        }
    }

    /**
     * Adds required css files
     */
    protected function addCSSFiles()
    {
        $path = '%s/templates/css/%s';
        $plugin_directory = $this->owner->getDirectory();
        $files = array("AgendaStep.css");

        foreach ($files as $file) {
            $css_file = sprintf(
                $path,
                $plugin_directory,
                $file
            );

            $this->tpl->addCSS($css_file);
        }
    }

    protected function addIddTotalCounter(\ilPropertyFormGUI $form)
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $legacy = $f->legacy(
            "<span>" . $this->txt("total_idd_time")
            . " </span><span id='total_idd_time'></span>"
        )
        ;

        $ci = new \ilCustomInputGUI("", "");
        $ci->setHtml($renderer->render($legacy));

        $form->addItem($ci);
    }

    /**
     * Adds the button to create a new agenda row
     *
     * @param \ilPropertyFormGUI 	$form
     *
     * @return void
     */
    protected function addNewRowButton(\ilPropertyFormGUI $form)
    {
        $btn = \ilJsLinkButton::getInstance();
        $btn->setCaption("add");
        $btn->setOnClick("il.TMS.agenda.addNewRow.rowAdding();");

        $cui = new \ilCustomInputGUI("", "btn");
        $cui->setHtml($btn->render());
        $form->addItem($cui);
    }

    /**
     * Adds the button to delete selected entries
     */
    protected function addDeleteButton(\ilPropertyFormGUI $form)
    {
        $btn = \ilSubmitButton::getInstance();
        $btn->setCaption("delete");
        $btn->setCommand(Player::COMMAND_SAVE);

        $cui = new \ilCustomInputGUI("", "btn");
        $cui->setHtml($btn->render());
        $form->addItem($cui);
    }

    /**
     * Adds the agenda table as form property
     */
    protected function addAgendaTable(\ilPropertyFormGUI $form)
    {
        $agenda_items = $this->getAgendaItemInfos();
        $this->addAgendaInfoToLookup($agenda_items);
        $table = $this->owner->getAgendaEntryTable();

        $cui = new \ilCustomInputGUI("", "table");
        $table->enabled["linkbar"] = false;
        $table->setData($this->getTableData());
        $table->setTitle('');
        $cui->setHtml($table->getHtml());

        $form->addItem($cui);
    }

    protected function addAgendaInfoToLookup($agenda_items)
    {
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
                'edit_fixed' => (int) $this->owner->editFixedBlocks(),
                'duration' => 0,
                'position' => 0
            ];

            $js = "il.TMS.agenda.itemselection.addToLookup(" . $item_id . ', ' . json_encode($data) . ");";
            $this->tpl->addOnLoadCode($js);
        }

        $this->tpl->addOnLoadCode('$(document).ready(il.TMS.agenda.itemselection.registerTimeInputEvents);');
    }

    protected function getTableData()
    {
        $existing_entries = $this->owner->getAgendaEntryDB()->selectFor((int) $this->owner->getId());

        if (count($existing_entries) == 0) {
            $existing_entries[] = $this->getDefaultTableData();
        }

        return $this->createProcessingArray($existing_entries);
    }

    protected function createProcessingArray(array $entries) : array
    {
        $ret = [];

        foreach ($entries as $entry) {
            $ret[] = array(
                "object" => $entry,
                "error" => array(),
                "delete" => false
            );
        }

        return $ret;
    }

    protected function getDefaultTableData() : AgendaEntry
    {
        return new AgendaEntry(
            -1,
            -1,
            null,
            0,
            10
        );
    }

    /**
     * Get all infos via ente
     *
     * @return AgendaItemInfo[]
     */
    protected function getAgendaItemInfos()
    {
        if ($this->agenda_item_infos === null) {
            $return = [];

            if (\ilPluginAdmin::isPluginActive('xaip') === true) {
                $agenda_pools = $this->getAllReadableAgendaItemPools();
                $agenda_items = array_map(
                    function (\ilObjAgendaItemPool $agenda_pool) {
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
     * Pads left leading zero
     */
    protected function padLeft(string $value) : string
    {
        return str_pad($value, 2, "0", STR_PAD_LEFT);
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
                return \ilObjectFactory::getInstanceByRefId($child_ref);
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
}
