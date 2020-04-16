<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\CourseMember;
use CaT\Plugins\CourseMember\Members\Member;
use CaT\Plugins\CourseMember\TableProcessing\TableProcessor;

class ilMembersGUI
{
    const CMD_SHOW_MEMBERS = "showMembers";
    const CMD_SAVE = "saveMembers";
    const CMD_CLOSE = "closeMembers";
    const CMD_CONFIRM_CLOSE = "confirmClose";
    const CMD_UPLOAD_FILE = "uploadFile";
    const CMD_DELETE_FILE = "deleteFile";
    const CMD_DOWNLOAD_FILE = "downloadFile";
    const CMD_CONFIRM_DELETE_FILE = "confirmDeleteFile";

    const F_LP_VALUE = "f_lp_value";
    const F_CREDITS = "f_credits";
    const F_USER_ID = "f_user_id";
    const F_CRS_ID = "f_crs_id";
    const F_LP_ID = "f_lp_id";
    const F_ILIAS_LP = "f_ilias_lp";
    const F_NEW_LP_ID = "f_new_lp_id";
    const F_UPLOAD_FILE = "f_upload_file";
    const F_IDD_LEARNING_TIME = "f_idd_learning_time";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var CourseMember\ilObjActions
     */
    protected $object_actions;

    /**
     * @var CourseMember\LPOptions\ilActions
     */
    protected $lp_options_actions;

    /**
     * @var CourseMember\FileStorage\ilFileStorage
     */
    protected $file_storage;

    /**
     * @var TableProcessor
     */
    protected $table_processor;

    /**
     * @var CourseMember\SignatureList\ilActions
     */
    protected $siglist_actions;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var array
     */
    protected $member_data = [];

    /**
     * @var ilCourseMemberPlugin
     */
    protected $plugin;

    protected static $mime_types = array(
        "application/pdf",
        "image/jpeg",
        "image/png",
        "text/csv",
        "application/vnd.ms-excel"
    );

    protected static $file_suffixes = array("*.pdf", "*.png", "*.jpg", "*.jpeg", "*.csv");

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        CourseMember\ilObjActions $object_actions,
        CourseMember\LPOptions\ilActions $lp_options_actions,
        TableProcessor $table_processor,
        CourseMember\SignatureList\ilActions $siglist_actions,
        Closure $txt,
        int $idd_learning_time = null
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->access = $access;
        $this->toolbar = $toolbar;

        $this->object_actions = $object_actions;
        $this->lp_options_actions = $lp_options_actions;
        $this->file_storage = $this->object_actions->getObject()->getFileStorage();
        $this->table_processor = $table_processor;
        $this->siglist_actions = $siglist_actions;
        $this->idd_learning_time = $idd_learning_time;
        $this->txt = $txt;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_MEMBERS:
                $this->showMembers();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_CLOSE:
                $this->close();
                break;
            case self::CMD_CONFIRM_CLOSE:
                $this->confirmClose();
                break;
            case self::CMD_UPLOAD_FILE:
                $this->uploadFile();
                break;
            case self::CMD_DELETE_FILE:
                $this->deleteFile();
                break;
            case self::CMD_DOWNLOAD_FILE:
                $this->downloadFile();
                break;
            case self::CMD_CONFIRM_DELETE_FILE:
                $this->confirmDeleteFile();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    protected function showMembers()
    {
        $members = $this->object_actions->getMemberWithSavedLPSatus();
        $processing = $this->createProcessing($members);
        $this->showTable($processing);
    }

    /**
     * @throws Exception
     */
    protected function showTable(array $processing)
    {
        $edit_lp = $this->access->checkAccess("edit_lp", "", $this->object_actions->getObject()->getRefId());

        if ($edit_lp) {
            $this->setToolbar();
        }

        $table = new CourseMember\Members\ilMembersTableGUI(
            $this,
            $this->object_actions,
            $this->lp_options_actions,
            $edit_lp,
            null,
            $this->idd_learning_time
        );

        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setMaxCount(count($processing));
        $table->determineOffsetAndOrder();

        $processing = $this->sortData($processing, $table->getOrderField(), $table->getOrderDirection());
        $table->setData($processing);

        if (!$this->object_actions->getObject()->getSettings()->getClosed()
            && $edit_lp
        ) {
            $table->addCommandButton(self::CMD_SAVE, $this->txt("caching"));
            $table->addCommandButton(self::CMD_CONFIRM_CLOSE, $this->txt("close"));
        }

        $this->tpl->setContent($table->getHtml());
    }

    protected function sortData(array $data, $order_field, $order_direction) : array
    {
        $fnc = function ($a, $b) {
            return 0;
        };

        switch ($order_field) {
            case "firstname":
                if ($order_direction == "asc") {
                    $fnc = function ($a, $b) {
                        $obj_a = $a["object"];
                        $obj_b = $b["object"];

                        return strcasecmp($obj_a->getFirstname(), $obj_b->getFirstname());
                    };
                } elseif ($order_direction == "desc") {
                    $fnc = function ($a, $b) {
                        $obj_a = $a["object"];
                        $obj_b = $b["object"];

                        return strcasecmp($obj_b->getFirstname(), $obj_a->getFirstname());
                    };
                }
                break;
            case "lastname":
                if ($order_direction == "asc") {
                    $fnc = function ($a, $b) {
                        $obj_a = $a["object"];
                        $obj_b = $b["object"];
                        return strcasecmp($obj_a->getLastname(), $obj_b->getLastname());
                    };
                } elseif ($order_direction == "desc") {
                    $fnc = function ($a, $b) {
                        $obj_a = $a["object"];
                        $obj_b = $b["object"];
                        return strcasecmp($obj_b->getLastname(), $obj_a->getLastname());
                    };
                }
                break;
        }

        uasort($data, $fnc);

        return $data;
    }

    /**
     * Set toolbar for file upload
     *
     * @return void
     * @throws Exception
     */
    protected function setToolbar()
    {
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this), true);
        $closed = $this->object_actions->getObject()->getSettings()->getClosed();
        if (!$closed && $this->file_storage->isEmpty()) {
            require_once "Services/Form/classes/class.ilFileInputGUI.php";
            $file = new ilFileInputGUI($this->txt("select_file"), self::F_UPLOAD_FILE);
            $this->toolbar->addInputItem($file);
            $this->toolbar->addFormButton($this->txt("upload_file"), self::CMD_UPLOAD_FILE);

            if (!\ilPluginAdmin::isPluginActive('docdeliver')) {
                return;
            }

            $parent = $this->getParentCourse();
            $default_template_id = $this->getDefaultTemplateId();
            if (is_null($parent) && is_null($default_template_id)) {
                return;
            }

            $crs_id = (int) $parent->getId();
            $template_id = $this->getCourseSelectedTemplate($crs_id);
            if (is_null($template_id)) {
                $template_id = $default_template_id;
            }

            /** @var ilDocumentDeliveryPlugin $xcmb */
            $docdeliver = \ilPluginAdmin::getPluginObjectById('docdeliver');
            $link = $docdeliver->getLinkForSignatureList($crs_id, $template_id);

            $btn = \ilLinkButton::getInstance();
            $btn->setUrl($link);
            $btn->setCaption($this->txt('download_blank_file'), false);
            $btn->setTarget('_blank');
            $this->toolbar->addButtonInstance($btn);
        } else {
            if (!$this->file_storage->isEmpty()) {
                $this->toolbar->addFormButton($this->txt("download_file"), self::CMD_DOWNLOAD_FILE);
            }

            if (!$closed) {
                $this->toolbar->addFormButton($this->txt("delete_file"), self::CMD_CONFIRM_DELETE_FILE);
            }
        }
    }

    protected function save()
    {
        $post = $_POST;
        $processing = $this->getProcessingFrom($post);
        $processing = $this->table_processor->process($processing, array(TableProcessor::ACTION_SAVE));

        $errors = array_filter($processing, function ($p) {
            return count($p["errors"]) > 0;
        });

        if (count($errors) > 0) {
            ilUtil::sendSuccess($this->txt("save_successfull_partial"), true);
            $this->showTable($processing);
            return;
        }

        ilUtil::sendSuccess($this->txt("save_successfull"), true);
        $this->showMembers();
    }

    /**
     * @throws Exception
     */
    protected function confirmClose()
    {
        $post = $_POST;
        $processing = $this->getProcessingFrom($post);
        $processing = $this->table_processor->process($processing, array(TableProcessor::ACTION_SAVE));
        $confirm_question = $this->txt("confirm_closing");

        $errors = array_filter($processing, function ($p) {
            return count($p["errors"]) > 0 || is_null($p["object"]->getLPValue());
        });

        if (count($errors) > 0) {
            ilUtil::sendInfo($this->txt("no_close"), true);
            $this->showTable($processing);
            return;
        }

        if ($this->object_actions->getObject()->getSettings()->getListRequired()
            && $this->file_storage->isEmpty()
        ) {
            ilUtil::sendFailure($this->txt("no_list_uploaded"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
        }

        if (!is_null($this->idd_learning_time) && $this->idd_learning_time != 0) {
            $members = $this->object_actions->getMemberWithSavedLPSatus();
            $passed_members_without_idd_time = $this->getPassedMembersWithoutIddTime($members);
            if (count($passed_members_without_idd_time) > 0) {
                $confirm_question = $this->txt("hint_idd_equals_null");
            }
        }

        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($confirm_question);
        $confirmation->setConfirm($this->txt("close"), self::CMD_CLOSE, "submitBtn");
        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_MEMBERS, "cancelBtn");
        $confirmation->addHiddenItem("data", base64_encode(serialize($processing)));

        $this->tpl->setContent($confirmation->getHtml());
        $this->addSubmitJavascript();
    }

    protected function handleNoLPError(array $members)
    {
        $names = array_map(
            function (Member $member) {
                return $member->getLastname() . " " . $member->getFirstname();
            },
            $members
        );

        uasort($names, function (string $a, string $b) {
            return strcasecmp($a, $b);
        });

        $tpl = new ilTemplate(
            "tpl.members_not_finalizeable.html",
            true,
            true,
            $this->object_actions->getObject()->getDirectory()
        );

        $tpl->setVariable("HEADER", $this->txt("set_lp_to_all"));
        foreach ($names as $name) {
            $tpl->setCurrentBlock("members");
            $tpl->setVariable("MEMBER", $name);
            $tpl->parseCurrentBlock();
        }

        ilUtil::sendInfo($tpl->get(), true);
    }

    protected function addSubmitJavascript()
    {
        $dir = $this->object_actions->getObject()->getDirectory();
        $this->tpl->addJavaScript($dir . "/templates/js/deactivateButton.js");
    }

    /**
     * @throws ilTemplateException
     */
    protected function uploadFile()
    {
        $file_info = $_FILES[self::F_UPLOAD_FILE];

        if (!$file_info["tmp_name"]) {
            $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
        }

        if (!in_array($file_info["type"], self::$mime_types)) {
            $tpl = new ilTemplate("tpl.file_suffix.html", true, true, $this->object_actions->getObject()->getDirectory());
            $tpl->setVariable("MESSAGE", $this->txt("file_type_not_allowed"));
            foreach (self::$file_suffixes as $key => $value) {
                $tpl->setCurrentBlock("suffix");
                $tpl->setVariable("SUFFIX", $value);
                $tpl->parseCurrentBlock();
            }
            ilUtil::sendFailure($tpl->get(), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
        }

        if (!$this->file_storage->uploadFile($file_info)) {
            ilUtil::sendFailure($this->txt("file_could_not_be_uploaded"), true);
        } else {
            ilUtil::sendSuccess($this->txt("file_uploaded"), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function downloadFile()
    {
        $file_path = $this->file_storage->getFilePath();

        $filename = basename($file_path);
        ilUtil::deliverFile($file_path, $filename);
    }

    protected function confirmDeleteFile()
    {
        require_once "./Services/Utilities/classes/class.ilConfirmationGUI.php";
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("confirm_deleting_file"));
        $confirmation->setConfirm($this->txt("delete_file"), self::CMD_DELETE_FILE);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_SHOW_MEMBERS);

        $this->tpl->setContent($confirmation->getHtml());
    }

    protected function deleteFile()
    {
        $this->file_storage->deleteCurrentFile();
        ilUtil::sendSuccess($this->txt("file_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    /**
     * @throws Exception
     */
    protected function close()
    {
        $members = $this->object_actions->getMemberWithSavedLPSatus();
        $members_without_lp_state = $this->getMembersWithoutLPState($members);
        if (count($members_without_lp_state) > 0) {
            $this->handleNoLPError($members_without_lp_state);
            $this->showMembers();
            return;
        }

        $fnc = function ($s) {
            return $s->withClosed(true);
        };

        $this->object_actions->getObject()->updateSettings($fnc);
        $this->object_actions->getObject()->update();
        $this->object_actions->refreshLP();

        $parent = $this->object_actions->getObject()->getParentCourse();
        if (!is_null($parent)) {
            foreach ($members as $member) {
                $minutes = $member->getIDDLearningTime();
                $lp_value = $member->getLPValue();
                if (!is_null($minutes)) {
                    $this->object_actions->throwEvent(
                        (int) $parent->getId(),
                        $member->getUserId(),
                        $minutes,
                        $lp_value
                    )
                    ;
                }
            }
        }

        $this->object_actions->throwFinalizedEvent();

        ilUtil::sendSuccess($this->txt("save_successfull_and_closed"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function getMembersWithoutLPState(array $members) : array
    {
        $non_lp_members = array_filter(
            $members,
            function (CourseMember\Members\Member $member) {
                return is_null($member->getLPValue());
            }
        );
        return $non_lp_members;
    }

    protected function createProcessing(array $options) : array
    {
        $ret = array();

        foreach ($options as $option) {
            $ret[] = array("object" => $option, "delete" => false, "errors" => array(), "message" => array());
        }

        return $ret;
    }

    protected function getProcessingFrom(array $post) : array
    {
        $ret = array();
        foreach ($this->getMembersFrom($post) as $key => $member) {
            $ret[$key] = array("object" => $member, "errors" => array(), "message" => array());
            $ret[$key]["delete"] = false;
        }

        return $ret;
    }

    /**
     * @param array $value
     * @return CourseMember\Members\Member[]
     */
    protected function getMembersFrom(array $value) : array
    {
        $ret = array();
        $user_ids = $value[self::F_USER_ID];
        if (is_null($user_ids)) {
            $user_ids = array();
        }

        foreach ($user_ids as $key => $user_id) {
            $crs_id = (int) $value[self::F_CRS_ID][$key];
            $lp_id = $this->getIntOrNull($value[self::F_LP_ID][$key]);
            $lp_value = $this->getStringOrNull($value[self::F_LP_VALUE][$key]);
            $new_lp_id = $this->getIntOrNull($value[self::F_NEW_LP_ID][$key]);
            $ilias_lp = $this->getIntOrNull($value[self::F_ILIAS_LP][$key]);
            $credits = $this->getFloatOrNull($value[self::F_CREDITS][$key]);
            $idd_learning_time = $this->timeToIntOrNull($value[self::F_IDD_LEARNING_TIME][$key]);

            if ($new_lp_id === null) {
                require_once("Services/Tracking/classes/class.ilLPStatus.php");
                $lp_value = null;
                $ilias_lp = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
                $lp_id = $new_lp_id;
            } elseif ($new_lp_id != $lp_id) {
                $lp_value = $this->lp_options_actions->getLPOptionTitleBy($new_lp_id);
                $ilias_lp = $this->lp_options_actions->getILIASLPBy($new_lp_id);
                $lp_id = $new_lp_id;
            }

            $mem = $this->object_actions->getMemberWith((int) $user_id, $crs_id, $lp_id, $lp_value, $ilias_lp, $credits, $idd_learning_time);

            $usr_data = ilObjUser::_lookupName($user_id);
            $ret[] = $mem
                ->withFirstname($usr_data["firstname"])
                ->withLastname($usr_data["lastname"])
                ->withLogin($usr_data["login"]);
        }

        return $ret;
    }

    /**
     * @param CourseMember\Members\Member[] $members
     * @return CourseMember\Members\Member[]
     */
    protected function getPassedMembersWithoutIddTime(array $members) : array
    {
        return array_filter($members, function (CourseMember\Members\Member $member) {
            return ($member->getILIASLP() == ilLPStatus::LP_STATUS_COMPLETED_NUM) &&
                   ($member->getIDDLearningTime() == 0);
        });
    }

    /**
     * @return int | null
     */
    protected function getIntOrNull($value)
    {
        if ($value == "" || is_null($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return float | null
     */
    protected function getFloatOrNull($value)
    {
        if ($value == "" || is_null($value)) {
            return null;
        }

        return floatval($this->replaceComma($value));
    }

    /**
     * Get the value transformed to minutes
     *
     * @param string[] | null	$value
     *
     * @return int | null
     */
    protected function timeToIntOrNull(array $value = null)
    {
        if (is_null($value)) {
            return null;
        }
        return $value["hh"] * 60 + $value["mm"];
    }

    /**
     * Replace last comma of value with an dot
     *
     * @param string 	$value
     *
     * @return string
     */
    protected function replaceComma(string $value)
    {
        $pos = strrpos($value, ",");

        if ($pos !== false) {
            $value = substr_replace($value, ".", $pos, strlen(","));
        }

        return $value;
    }

    /**
     * Get the value as string or null
     *
     * @param string 	$value
     *
     * @return string | null
     */
    protected function getStringOrNull(string $value)
    {
        if ($value == "" || is_null($value)) {
            return null;
        }

        return $value;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function getParentCourse()
    {
        return $this->object_actions->getObject()->getParentCourse();
    }

    protected function getDefaultTemplateId()
    {
        return $this->getPluginObject()->getMemberListDefaultTemplateId();
    }

    protected function getCourseSelectedTemplate(int $crs_id)
    {
        return $this->getPluginObject()->getSelectedCourseTemplate($crs_id);
    }

    protected function getPluginObject() : ilCourseMemberPlugin
    {
        if (is_null($this->plugin)) {
            $this->plugin = $this->object_actions->getObject()->getPluginObject();
        }
        return $this->plugin;
    }
}
