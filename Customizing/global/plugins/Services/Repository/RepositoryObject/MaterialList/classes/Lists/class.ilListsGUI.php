<?php

use \CaT\Plugins\MaterialList;
use \CaT\Plugins\MaterialList\Lists;

class ilListsGUI
{
    const CMD_SHOW_CONTENT = "showContent";
    const CMD_ADD_ENTRY = "addEntry";
    const CMD_EXPORT_LIST = "exportList";
    const CMD_EXPORT_ALL_LISTS = "exportAllLists";
    const CMD_SAVE_ENTRIES = "saveEntries";
    const CMD_DELETE_ENTRIES = "deleteEntries";
    const CMD_REQUEST_DELETE_ENTRIES = "requestDeleteEntries";

    /**
     * @var \ilObjMaterialListGUI
     */
    protected $parent_gui;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var \ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var \ilAccessHandler
     */
    protected $g_access;

    public function __construct(
        \ilObjMaterialListGUI $parent_gui,
        MaterialList\ilObjectActions $object_actions,
        MaterialList\ilPluginActions $plugin_actions,
        ilMaterialListPlugin $plugin,
        \Closure $txt,
        MaterialList\Materials\ListEntryValidate $list_entry_validate
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_access = $DIC->access();

        $this->parent_gui = $parent_gui;
        $this->object_actions = $object_actions;
        $this->plugin_actions = $plugin_actions;
        $this->txt = $txt;
        $this->list_entry_validate = $list_entry_validate;
        $this->g_tpl->addCss('./Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/templates/design.css');
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_CONTENT:
                $this->setToolbar();
                $this->showContent();
                break;
            case self::CMD_ADD_ENTRY:
                $this->setToolbar();
                $this->addEntry();
                break;
            case self::CMD_EXPORT_LIST:
                $this->exportList();
                break;
            case self::CMD_EXPORT_ALL_LISTS:
                $this->exportAllLists();
                break;
            case self::CMD_SAVE_ENTRIES:
                $this->saveEntries();
                break;
            case self::CMD_REQUEST_DELETE_ENTRIES:
                $this->requestDeleteEntries();
                break;
            case self::CMD_DELETE_ENTRIES:
                $this->deleteEntries();
                break;
            case "getArticleNumberAndTitle":
                $this->getArticleNumberAndTitle();
                break;
            default:
                throw new \Exception(__METHOD__ . ":: unknown command: " . $cmd);
        }
    }

    /**
     * Show lists content
     *
     * @return null
     */
    protected function showContent()
    {
        $list_entries = $this->object_actions->getListEntiesForCurrentObj();
        $table = $this->initTable($list_entries);

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Add new empty list entry lines
     *
     * @return null
     */
    protected function addEntry()
    {
        $post = $_POST;
        if (!$this->checkNumberOfNewRows($post)) {
            \ilUtil::sendInfo(sprintf($this->txt("material_new_reduced"), MaterialList\ilObjectActions::MATERIAL_NUMBER_NEW_ROWS));
        }

        $check_objects = $this->getCheckObjectsFromPost($post);
        $list_entries = $this->addNewCheckObjects($post, $check_objects);
        $table = $this->initTable($list_entries);

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Get empty material objects according to entered number of new
     *
     * @param string[]
     * @param \CaT\Plugins\MaterialList\Materials\Material[] | []
     *
     * @return \CaT\Plugins\MaterialList\Materials\Material[] | []
     */
    protected function addNewCheckObjects(array $post, array $list_entries)
    {
        $number_of_new = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_NEW_LINE];

        for ($i = 0; $i < $number_of_new; $i++) {
            $list_entries[] = $this->object_actions->getNewCheckObject();
        }

        return $list_entries;
    }

    /**
     * Check if number of new row is greater then wished an reduce
     *
     * @param string[] 	$post
     *
     * @return boolean
     */
    protected function checkNumberOfNewRows(&$post)
    {
        $number_of_new = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_NEW_LINE];

        if ($number_of_new > MaterialList\ilObjectActions::MATERIAL_NUMBER_NEW_ROWS) {
            $post[MaterialList\ilObjectActions::F_LIST_ENTRY_NEW_LINE] = MaterialList\ilObjectActions::MATERIAL_NUMBER_NEW_ROWS;
            return false;
        }

        return true;
    }

    /**
     * Get old list entries from post
     *
     * @param string[] 	$post
     *
     * @return Lists\CheckObject[] | []
     */
    protected function getCheckObjectsFromPost(array $post) : array
    {
        $list_entries = array();
        $counter = 0;

        $ids = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_HIDDEN_IDS];
        $number_per_participant = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_NUMPER_PER_PARTICIPANT];
        $number_per_course = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_NUMBER_PER_COURSE];

        if ($ids && is_array($ids)) {
            foreach ($ids as $key => $id) {
                $article_number_and_title = $post[MaterialList\ilObjectActions::F_LIST_ENTRY_TITLE . $counter];
                $vals = explode("-", trim($article_number_and_title));

                if (count($vals) < 2) {
                    $article_number = "";
                    $title = trim($vals[0]);
                } else {
                    $article_number = trim($vals[0]);
                    $title = trim($vals[1]);
                }

                $list_entries[] = $this->object_actions->getCheckObject(
                    (int) $id,
                    $number_per_participant[$key],
                    $number_per_course[$key],
                    $article_number,
                    $title
                );

                $counter++;
            }
        }

        return $list_entries;
    }

    /**
     * Save list entry items
     *
     * @return null
     */
    protected function saveEntries()
    {
        $post = $_POST;

        $entries = $this->getCheckObjectsFromPost($post);
        uasort($entries, function ($a, $b) {
            return strcmp($a->getArticleNumber() . $a->getTitle(), $b->getArticleNumber() . $b->getTitle());
        });

        $result = $this->checkEntries($entries);
        if ($result["tooMuch"]) {
            \ilUtil::sendInfo($this->txt("list_too_much_per_participant"), true);
        }

        $failed_entries = [];
        if (count($result["empty"]) > 0) {
            $failed_entries = $this->mergeArrays($failed_entries, $result["empty"]);
        }

        if (count($result["faults"]["objects"]) > 0) {
            $failed_entries = $this->mergeArrays($failed_entries, $result["faults"]["objects"]);
        }

        if (count($failed_entries) > 0) {
            $failed_entries = $this->mergeArrays($result["correct"], $failed_entries);
            \ilUtil::sendFailure($this->txt("list_faults_in_inputs"));
            $this->setToolbar();
            ksort($failed_entries);
            $table = $this->initTable($failed_entries);
            $table->setFaults($result["faults"]["values"]);
            $this->g_tpl->setContent($table->getHtml());
            return;
        }

        $entries = $this->transformCheckToListObject($result["correct"]);
        $entries = $this->aggregateEntries($entries);
        $this->object_actions->saveListEntries($entries);
        $this->object_actions->updateLastEditValuesOfCurrentObject();

        if (count($result["correct"]) > 0) {
            $this->object_actions->updateEvent();
            \ilUtil::sendSuccess($this->txt("list_success_save"), true);
        }

        $this->g_ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    protected function mergeArrays(array $a, array $b) : array
    {
        foreach ($b as $key => $value) {
            $a[$key] = $value;
        }
        return $a;
    }

    /**
     * Gets aggregated ListEntries
     *
     * @param Lists\ListEntry[] 	$entries
     *
     * @return Lists\ListEntry[];
     */
    protected function aggregateEntries($entries)
    {
        $ret = array();
        $article_title = null;

        foreach ($entries as $entry) {
            if ($entry->getArticleNumber() == "" && $entry->getTitle() == "") {
                $ret[] = $entry;
                continue;
            }

            if ($entry->getArticleNumber() . $entry->getTitle() == $article_title) {
                if ($this->listEntryShouldDeleted($entry->getId(), $org->getId())) {
                    $this->deleteListEntry($entry->getId());
                }

                continue;
            }

            $article_title = $entry->getArticleNumber() . $entry->getTitle();
            $same_article_number = $this->filterListEntriesWithArticleTitle($entries, $article_title);
            $org = $this->getListEntriesWithId($same_article_number, $entry);

            $ret[] = $this->aggregrate($org, $same_article_number, $article_title);
        }

        return $ret;
    }

    /**
     * Aggregate list entries with same article number and title
     *
     * @param List\ListEntry 	$org
     * @param List\ListEntry[] 	$same_article_number
     * @param string 	$article_title
     *
     * @return List\ListEntry
     */
    protected function aggregrate($org, $same_article_number, $article_title)
    {
        if (count($same_article_number) > 0) {
            $per_participant = 0;
            $per_course = 0;
            foreach ($same_article_number as $key => $same_article) {
                $per_participant += $same_article->getNumberPerParticipant();
                $per_course += $same_article->getNumberPerCourse();

                if ($this->listEntryShouldDeleted($same_article->getId(), $org->getId(), true, $article_title)) {
                    $this->deleteListEntry($same_article->getId());
                }
            }
            return $org->withNumberPerParticipant($per_participant)->withNumberPerCourse($per_course);
        }

        return $org;
    }

    /**
     * Checks list entry should be deleted
     *
     * @param int 	$entry_id
     * @param int 	$id_of_aggregate_obj
     * @param bool 	$check_art_title
     * @param string $article_title
     *
     * @return bool
     */
    protected function listEntryShouldDeleted($entry_id, $id_of_aggregate_obj, $check_art_title = false, $article_title = "")
    {
        if ($entry_id != -1 && $entry_id != $id_of_aggregate_obj) {
            if ($check_art_title) {
                $old_art_title = $this->object_actions->getArtTitleById($entry_id);

                if ($article_title == $old_art_title) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Deletes saved list entry
     *
     * @param int 	$entry_id
     * @param int 	$id_of_aggregate_obj
     * @param bool 	$check_art_title
     * @param string $article_title
     *
     * @return void
     */
    protected function deleteListEntry($entry_id)
    {
        $this->object_actions->deleteListEntry($entry_id);
    }

    /**
     * Get list entries with id
     *
     * @param Lists\ListEntry[] 	$entries
     * @param Lists\ListEntry 	$default
     *
     * @return Lists\ListEntry
     */
    protected function getListEntriesWithId($entries, $default)
    {
        $with_id = array_filter($entries, function ($e) {
            if ($e->getId() != -1) {
                return $e;
            }
        });

        if (count($with_id) > 0) {
            return array_shift($with_id);
        }

        return $default;
    }

    /**
     * Get list entries with same article and title
     *
     * @param Lists\ListEntry[] 	$entries
     * @param string 	$article_title
     *
     * @return Lists\ListEntry[]
     */
    protected function filterListEntriesWithArticleTitle($entries, $article_title)
    {
        return array_filter($entries, function ($e) use ($article_title) {
            if ($e->getArticleNumber() . $e->getTitle() == $article_title) {
                return $e;
            }
        });
    }

    /**
     * Check entries in post if there are valid or not
     *
     * @param Lists\CheckObject[] 	$entries
     *
     * @return array<string, int | string | Lists\CheckObject[] | string[]>
     */
    protected function checkEntries($entries)
    {
        $counter = 0;
        $ret = array();
        $ret["correct"] = array();
        $ret["faults"] = array();
        $ret["faults"]["objects"] = array();
        $ret["faults"]["values"] = array();
        $ret["empty"] = array();
        $ret["tooMuch"] = false;

        foreach ($entries as $entry) {
            $this->list_entry_validate->validateEntry($entry, $ret, $counter);
            $counter++;
        }

        return $ret;
    }

    protected function requestDeleteEntries()
    {
        $to_delete = [];
        if (array_key_exists(MaterialList\ilObjectActions::F_LIST_ENTRY_TO_DELETE_IDS, $_POST)) {
            $to_delete = array_map(
                function ($id) {
                    return (int) $id;
                },
                $_POST[MaterialList\ilObjectActions::F_LIST_ENTRY_TO_DELETE_IDS]
            );
        }
        if (count($to_delete) === 0) {
            $this->g_ctrl->redirect($this, self::CMD_SHOW_CONTENT);
        }
        $to_delete_titles = array_map(
            function ($list_entry) {
                return $list_entry->getTitle();
            },
            array_filter(
                $this->object_actions->getListEntiesForCurrentObj(),
                function ($list_entry) use ($to_delete) {
                    return in_array($list_entry->getId(), $to_delete);
                }
            )
        );
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText(
            sprintf($this->txt('confirmation_delete_entries'), implode(', ', $to_delete_titles))
        );

        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_CONTENT, "cancelBtn");
        $confirmation->setConfirm($this->txt("delete"), self::CMD_DELETE_ENTRIES, "submitBtn");
        foreach ($to_delete as $entry_id) {
            $confirmation->addHiddenItem(
                MaterialList\ilObjectActions::F_LIST_ENTRY_TO_DELETE_IDS . '[]',
                $entry_id
            );
        }
        $this->g_tpl->setContent($confirmation->getHtml());
    }

    /**
     * Delete selected list entries
     *
     * @return null
     */
    protected function deleteEntries()
    {
        $this->object_actions->deleteListEntries($_POST);
        $this->object_actions->updateLastEditValuesOfCurrentObject();
        $this->object_actions->deleteEvent();
        $this->g_ctrl->redirect($this, self::CMD_SHOW_CONTENT);
    }

    /**
     * Export list entries
     *
     * @return void
     */
    protected function exportList()
    {
        $xmat_obj = $this->object_actions->getObject();
        $exporter = $this->plugin_actions->getPlugin()->getListXLSExporter(array($xmat_obj));
        list($folder, $file_name) = $exporter->getFileLocation();
        $exporter->export();
        \ilUtil::deliverFile($folder . $file_name, $file_name, '', false, true);
    }

    /**
     * Export all lists of course where current object is child
     *
     * @return void
     */
    protected function exportAllLists()
    {
        $xmat_objs = $this->object_actions->getMaterialListOfParentCourse();

        $xmat_objs = array_filter($xmat_objs, function ($obj) {
            return $this->g_access->checkAccess("read", "", $obj->getRefId());
        });

        if (count($xmat_objs) === 0) {
            \ilUtil::sendInfo($this->txt("no_read_access_for_all_lists"), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_CONTENT);
        }

        $xmat_objs = array_values($xmat_objs);

        $exporter = $this->plugin_actions->getPlugin()->getListXLSExporter($xmat_objs);
        list($folder, $file_name) = $exporter->getFileLocation();
        $exporter->export();
        \ilUtil::deliverFile($folder . $file_name, $file_name, '', false, true);
    }

    /**
     * Render the list entries table
     *
     * @param Lists\ListEntry[] $list_entries
     *
     * @return Lists\ilListsTableGUI
     */
    protected function initTable(array $list_entries)
    {
        $write_access = $this->g_access->checkAccess("write", "", $this->object_actions->getObject()->getRefId());

        $table = new Lists\ilListsTableGUI($this, $this->plugin_actions, $this->txt, $write_access);
        $table->setData($list_entries);

        if ($write_access) {
            $table->addCommandButton(self::CMD_SAVE_ENTRIES, $this->txt("save"));
            $table->addMultiCommand(self::CMD_REQUEST_DELETE_ENTRIES, $this->txt("delete"));
        }

        return $table;
    }

    /**
     * Set the toolbar items
     *
     * @return null
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this));
        $this->g_toolbar->setCloseFormTag(false);

        $current_object_ref_id = $this->object_actions->getObject()->getRefId();

        if ($this->g_access->checkAccess("write", "", $current_object_ref_id)) {
            include_once "Services/Form/classes/class.ilTextInputGUI.php";
            $type = new ilTextInputGUI("", MaterialList\ilObjectActions::F_LIST_ENTRY_NEW_LINE);
            $type->setValue(1);
            $this->g_toolbar->addInputItem($type);
            $this->g_toolbar->addFormButton($this->txt("lists_add_entries"), self::CMD_ADD_ENTRY);
            $this->g_toolbar->addSeparator();
        }

        if ($this->object_actions->getObject()->getParentCourse() !== null && $this->g_access->checkAccess("visible", "", $current_object_ref_id)) {
            $this->g_toolbar->addFormButton($this->txt("lists_export_list"), self::CMD_EXPORT_LIST);
            $this->g_toolbar->addSeparator();
            $this->g_toolbar->addFormButton($this->txt("lists_export_all_lists"), self::CMD_EXPORT_ALL_LISTS);
        }
    }

    /**
     * Echo the option for autocomplete
     *
     * @return null
     */
    protected function getArticleNumberAndTitle()
    {
        include_once './Services/JSON/classes/class.ilJsonUtil.php';
        $options = $this->plugin_actions->getAutoCompleteOptions($_REQUEST['term']);
        echo ilJsonUtil::encode($options);
        exit;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        assert('is_string($code)');

        $txt = $this->txt;

        return $txt($code);
    }

    /**
     * @param Lists\CheckObject[]
     * @return Lists\ListEntry[]
     */
    protected function transformCheckToListObject(array $check_objects) : array
    {
        $ret = [];
        /** @var  $object Lists\CheckObject*/
        foreach ($check_objects as $object) {
            $ret[] = $this->object_actions->getListEntry(
                $object->getId(),
                (int) $object->getNumberPerParticipant(),
                (int) $object->getNumberPerCourse(),
                $object->getArticleNumber(),
                $object->getTitle()
            );
        }
        return $ret;
    }
}
