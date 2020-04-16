<?php

use \CaT\Plugins\Webinar\VC\CSN;
use \CaT\Plugins\Webinar\Exceptions;

class ilCSNGUI
{
    const CMD_SHOW_PARTICIPANTS = "showParticipants";
    const CMD_AUTOCOMPLETE = "userfieldAutocomplete";
    const F_LOGIN_FILTER = "f_login";
    const CMD_APPLY_FILTER = "applyFilter";
    const CMD_RESET_FILTER = "resetFilter";
    const CMD_FINISH = "finish";
    const CMD_CONFIRM_FINISH = "confirmFinish";

    /**
     * @var \ilParticipantGUI
     */
    protected $parent;

    /**
     * @var CSN\ilActions
     */
    protected $vc_actions;

    /**
     * @var \CaT\Plugins\Webinar\ilActions
     */
    protected $actions;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilAccessHandler
     */
    protected $g_access;

    public function __construct(\ilParticipantGUI $parent, CSN\ilActions $vc_actions, \CaT\Plugins\Webinar\ilActions $actions)
    {
        $this->parent = $parent;
        $this->vc_actions = $vc_actions;
        $this->actions = $actions;

        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_access = $DIC->access();
        $this->determineFilterValues();
    }

    /**
     * Execute commands for this gui
     *
     * @return null
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_SHOW_PARTICIPANTS);

        switch ($cmd) {
            case self::CMD_SHOW_PARTICIPANTS:
                $this->showParticipants();
                break;
            case self::CMD_APPLY_FILTER:
                $this->applyFilter();
                break;
            case self::CMD_RESET_FILTER:
                $this->resetFilter();
                break;
            case self::CMD_AUTOCOMPLETE:
                $this->userfieldAutocomplete();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    /**
     * Show members of CSN VC and their lp
     *
     * @return null
     */
    protected function showParticipants()
    {
        $table = new CSN\ilCSNTableGUI($this, $this->vc_actions, $this->actions, self::CMD_SHOW_PARTICIPANTS);
        $table->determineOffsetAndOrder();
        $limit = (int) $table->getLimit();
        $offset = (int) $table->getOffset();
        $order_function = $table->getOrderField();
        $order_direction = $table->getOrderDirection();

        $data = $this->vc_actions->getAllParticipants();
        $n_data = array();

        if ($this->tags_filter_value) {
            $data = array_filter($data, function ($dt) {
                if ($dt->getUserName() == $this->tags_filter_value) {
                    return $dt;
                }
            });
        }

        for ($i = 0; $i < $offset + $limit; $i++) {
            if ($i >= $offset && array_key_exists($i, $data)) {
                $n_data[$i] = $data[$i];
            }
        }

        if ($order_function) {
            $n_data = $this->$order_function($n_data, $order_direction);
        }

        $table->setData($n_data);
        $table->setMaxCount(count($data));
        $table->setFilterValues(array(self::F_LOGIN_FILTER => $this->tags_filter_value));

        if (!$this->vc_actions->getObject()->getSettings()->isFinished()) {
            $table->addCommandButton(self::CMD_CONFIRM_FINISH, $this->txt("finish"));
        }

        $this->fillFilterItem($table);
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Sorts data by name
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByName($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            return strcmp($a->getName(), $b->getName());
        });

        if ($order_direction == "asc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Sorts data by login
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByLogin($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            return strcmp($a->getUserName(), $b->getUserName());
        });

        if ($order_direction == "asc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Sorts data by mail address
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByMail($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            return strcmp($a->getEmail(), $b->getEmail());
        });

        if ($order_direction == "asc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Sorts data by phone
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByPhone($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            return strcmp($a->getPhone(), $b->getPhone());
        });

        if ($order_direction == "asc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Sorts data by minutes
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByMinutes($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            return strcmp((string) $a->getMinutes(), (string) $b->getMinutes());
        });

        if ($order_direction == "asc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Sorts data by status
     *
     * @param Participant[] 	$data
     * @param string 	$order_direction
     *
     * @return Participant[]
     */
    protected function sortByStatus($data, $order_direction)
    {
        uasort($data, function ($a, $b) {
            $status_a = -1;
            if ($a->isKnownUser()) {
                $status_a = $this->vc_actions->getObject()->getLPStatusForUser($a->getUserId());
            }

            $status_b = -1;
            if ($b->isKnownUser()) {
                $status_b = $this->vc_actions->getObject()->getLPStatusForUser($b->getUserId());
            }

            return strcmp((string) $status_a, (string) $status_b);
        });

        if ($order_direction == "desc") {
            $data = array_reverse($data);
        }

        return $data;
    }

    /**
     * Determin filter values from get
     *
     * @return null
     */
    protected function determineFilterValues()
    {
        $this->tags_filter_value = "";
        $filter_values = $_GET["filter_values"];
        if ($filter_values) {
            $filter_values = unserialize(base64_decode($filter_values));
            $this->tags_filter_value = $filter_values[self::F_LOGIN_FILTER];
        }
    }

    /**
     * Create table filter
     *
     * @param CSN\ilCSNTableGUI 	$table
     *
     * @return null
     */
    protected function fillFilterItem(CSN\ilCSNTableGUI $table)
    {
        require_once("Services/Form/classes/class.ilTextInputGUI.php");
        $autocomplete_link = $this->g_ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true);
        $ti = new \ilTextInputGUI($this->txt("user_name"), self::F_LOGIN_FILTER);
        $ti->setDataSource($autocomplete_link);
        $ti->setValue($this->tags_filter_value);
        $table->addFilterItem($ti);
    }

    /**
     * Applies user filter entry to class property
     *
     * @return void
     */
    protected function applyFilter()
    {
        $this->tags_filter_value = "";
        $post = $_POST;
        if (isset($post[self::F_LOGIN_FILTER]) && $post[self::F_LOGIN_FILTER] != "") {
            $this->tags_filter_value = $post[self::F_LOGIN_FILTER];
        }

        $this->showParticipants();
    }

    /**
     * Reset all filter values
     *
     * @return void
     */
    protected function resetFilter()
    {
        $this->tags_filter_value = "";
        $this->showParticipants();
    }

    /**
     * Translate code
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        return $this->vc_actions->getObject()->pluginTxt($code);
    }

    /**
     * Asynch function to fill the user search input element
     *
     * @return void
     */
    protected function userfieldAutocomplete()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->enableFieldSearchableCheck(false);
        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    /**
     * Add toolbar items
     *
     * @param ilToolbarGUI 	$toolbar
     *
     * @return void
     */
    public function addToolbarItems(ilToolbarGUI $toolbar)
    {
        require_once(__DIR__ . "/../../Participant/class.ilParticipantGUI.php");
        $parent_crs = $this->vc_actions->getObject()->getParentCourse();
        $object = $this->vc_actions->getObject();
        $settings = $this->vc_actions->getObject()->getSettings();
        $finished = $settings->isFinished();

        $file_storage = $object->getFileStorage();
        $vc_type = $object->getSettings()->getVCType();
        $edit_member = $this->g_access->checkAccess("edit_member", "", $object->getRefId());
        $edit_participation = $this->g_access->checkAccess("edit_participation", "", $object->getRefId());
        $parent_crs = $object->getParentCourse();

        if ($edit_participation) {
            if ($file_storage->isEmpty() && !$finished) {
                require_once "Services/Form/classes/class.ilFileInputGUI.php";
                $file = new ilFileInputGUI($this->txt("csv_upload_list"), ilParticipantGUI::F_IMPORT_FILE);
                $toolbar->addInputItem($file, true);
                $toolbar->addFormButton($this->txt("upload_" . strtolower($vc_type)), ilParticipantGUI::CMD_IMPORT);
                $toolbar->addSeparator();
            } elseif (!$file_storage->isEmpty()) {
                if (!$finished) {
                    $toolbar->addFormButton($this->txt("delete_" . strtolower($vc_type)), ilParticipantGUI::CMD_DELETE);
                }
                $toolbar->addFormButton($this->txt("download_" . strtolower($vc_type)), ilParticipantGUI::CMD_DOWNLOAD);
                $toolbar->addSeparator();
            }
        }

        if ($edit_member && $parent_crs) {
            $toolbar->addFormButton($this->txt("download_new_" . strtolower($vc_type)), ilParticipantGUI::CMD_EXPORT);

            $settings = $this->vc_actions->getObject()->getSettings();
            if (!$settings->isFinished()) {
                $toolbar->addSeparator();
                $toolbar->addFormButton($this->txt("book_member_from_kurs"), ilParticipantGUI::CMD_BOOK_MEMBER_FROM_COURSE);
            }
        }
    }
}
