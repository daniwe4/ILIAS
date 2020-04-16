<?php

declare(strict_types=1);

use CaT\Plugins\BookingModalities\ilActions;

/**
 * Config GUI to upload a document which is then shown as downloadable in
 * booking steps.
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilDownloadableDocumentGUI
{
    const CMD_SHOW_DOC = "showDoc";
    const CMD_SAVE_DOC = "saveDoc";
    const CMD_ASSIGN_DOC = "assignDoc";
    const CMD_SAVE_ASSIGNMENT = "saveAssignments";

    const F_MODALITIES_DOC = "f_modalities_doc";
    const F_MODALITIES_DOC_DELETE = "f_modalities_doc_delete";
    const F_ASSIGN_PREFIX = "f_grole_id";
    const F_ASSIGN_DEFAULT = "0";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var \ilBookingModalitiesConfigGUI
     */
    protected $parent;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var \FileStorage
     */
    protected $file_storage;

    public function __construct(\ilBookingModalitiesConfigGUI $parent, ilActions $actions)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->parent = $parent;
        $this->actions = $actions;
        $this->file_storage = $this->actions->getFileStorage();
    }

    /**
     * @inheritdoc
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd(self::CMD_ASSIGN_DOC);
        $this->setSubTabs();
        switch ($cmd) {
            case self::CMD_SHOW_DOC:
                $this->g_tabs->setSubTabActive($cmd);
                $this->showDoc();
                break;
            case self::CMD_SAVE_DOC:
                $this->saveDoc();
                break;

            case self::CMD_ASSIGN_DOC:
                $this->g_tabs->setSubTabActive($cmd);
                $this->assignDoc();
                break;
            case self::CMD_SAVE_ASSIGNMENT:
                $this->saveAssignment();
                break;

            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    /**
     * Add sub-tabs.
     */
    protected function setSubTabs()
    {
        $this->g_tabs->addSubTab(
            self::CMD_ASSIGN_DOC,
            $this->txt('assign_roles'),
            $this->g_ctrl->getLinkTarget($this, self::CMD_ASSIGN_DOC)
        );

        $this->g_tabs->addSubTab(
            self::CMD_SHOW_DOC,
            $this->txt('upload_documents'),
            $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW_DOC)
        );
    }

    /**
     * Show upload form
     */
    public function showDoc(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initDocsForm();
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Show form for role-assignment
     */
    public function assignDoc(ilPropertyFormGUI $form = null)
    {
        if ($form === null) {
            $form = $this->initAssignForm();
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Save uploads
     */
    public function saveDoc()
    {
        $form = $this->initDocsForm();
        $file_upload_status = null;
        $file_delete_status = null;

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showDoc($form);
            return;
        }

        $post = $_POST;

        $doc_info = $post[self::F_MODALITIES_DOC];
        $del = $post[self::F_MODALITIES_DOC_DELETE];

        if ($doc_info['size'] > 0) {
            $file_upload_status = $this->uploadFile($doc_info);
        }

        if (count($del) > 0) {
            $file_delete_status = $this->deleteFiles($del);
        }

        $this->generateMessage($file_upload_status, $file_delete_status);
        $this->showDoc();
    }

    protected function uploadFile(array $file_infos) : bool
    {
        return $this->file_storage->uploadFile($file_infos);
    }

    protected function deleteFiles(array $files) : bool
    {
        $all_files_deleted = true;

        foreach ($files as $file) {
            if (!$this->file_storage->deleteSingleFile($file)) {
                $all_files_deleted = false;
            }
        }

        return $all_files_deleted;
    }

    protected function generateMessage(bool $upload = null, bool $delete = null)
    {
        if (!is_null($upload) && !is_null($delete)) {
            if ($upload && $delete) {
                \ilUtil::sendSuccess($this->txt('file_uploaded_and_deleted'));
            } elseif ($upload) {
                \ilUtil::sendSuccess($this->txt('file_uploaded'));
                \ilUtil::sendFailure($this->txt('file_could_not_be_deleted'), true);
            } elseif ($delete) {
                \ilUtil::sendFailure($this->txt('file_could_not_be_uploaded'));
                \ilUtil::sendSuccess($this->txt('file_deleted'), true);
            } else {
                \ilUtil::sendFailure($this->txt('file_could_not_be_uploaded_and_file_could_not_be_deleted'));
            }
        } elseif (!is_null($upload)) {
            if ($upload) {
                \ilUtil::sendSuccess($this->txt('file_uploaded'));
            } else {
                \ilUtil::sendFailure($this->txt('file_could_not_be_uploaded'));
            }
        } elseif (!is_null($delete)) {
            if ($delete) {
                \ilUtil::sendSuccess($this->txt('file_deleted'), true);
            } else {
                \ilUtil::sendFailure($this->txt('file_could_not_be_deleted'), true);
            }
        } else {
            \ilUtil::sendInfo($this->txt('no_file_selected'));
        }
    }

    /**
     * Save assingments
     */
    public function saveAssignment()
    {
        $form = $this->initAssignForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->assignDoc($form);
            return;
        }

        $post = $_POST;
        $assigns = $this->actions->getDocumentRoleAssignments();

        foreach ($assigns as $assign) {
            $v = $post[self::F_ASSIGN_PREFIX . $assign->getRoleId()];
            $nu_assign = $assign->withFileName($v);
            $this->actions->updateDocumentRoleAssignment($nu_assign);
        }

        \ilUtil::sendSuccess($this->txt('assignment_saved'));
        $this->assignDoc();
    }

    protected function initDocsForm() : ilPropertyFormGUI
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("modalities_doc"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        require_once "Services/Form/classes/class.ilFileInputGUI.php";
        $file = new ilFileInputGUI($this->txt("modalities_doc_upload"), self::F_MODALITIES_DOC);
        $file->setSuffixes(array('pdf'));
        $form->addItem($file, true);

        $opts = array();
        $fs = $this->actions->getFileStorage();
        foreach ($fs->readDir() as $filename) {
            $opts[$filename] = $filename;
        }
        $ms = new ilMultiSelectInputGUI($this->txt("modalities_doc_delete"), self::F_MODALITIES_DOC_DELETE);
        $ms->setWidth(350);
        $ms->setOptions($opts);
        $form->addItem($ms);

        $form->addCommandButton(self::CMD_SAVE_DOC, $this->txt("save"));
        return $form;
    }

    protected function initAssignForm() : ilPropertyFormGUI
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt("modalities_assignments"));
        $form->setFormAction($this->g_ctrl->getFormAction($this));

        $opts = array(
            '' => $this->txt('modalities_assignments_default_option'),
            $this->actions->getNoFileConst() => $this->txt('modalities_assignments_no_file')
        );
        $fs = $this->actions->getFileStorage();
        foreach ($fs->readDir() as $filename) {
            $opts[$filename] = $filename;
        }

        $assigns = $this->actions->getDocumentRoleAssignments();
        $values = array();
        foreach ($assigns as $assign) {
            $values[self::F_ASSIGN_PREFIX . $assign->getRoleId()] = $assign->getFileName();
        }

        //default:
        $fieldname = self::F_ASSIGN_PREFIX . self::F_ASSIGN_DEFAULT;
        $si = new ilSelectInputGUI($this->txt('modalities_assignments_defaults'), $fieldname);
        $si->setInfo($this->txt('modalities_assignments_default_info'));
        $si->setOptions(array_slice($opts, 1));
        $si->setValue($values[$fieldname]);
        $form->addItem($si);

        //global roles
        foreach ($this->actions->getDocumentRoleDB()->getGlobalRoles() as $id => $title) {
            $fieldname = self::F_ASSIGN_PREFIX . $id;
            $si = new ilSelectInputGUI($title, $fieldname);
            $si->setOptions($opts);
            $si->setValue($values[$fieldname]);
            $form->addItem($si);
        }

        $form->addCommandButton(self::CMD_SAVE_ASSIGNMENT, $this->txt("save"));
        return $form;
    }

    /**
     * Translate code to text
     */
    protected function txt(string $code) : string
    {
        return $this->actions->getPlugin()->txt($code);
    }
}
