<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\WBDManagement\Settings\WBDManagement;
use CaT\Plugins\WBDManagement\Settings\FileStorage;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilWBDManagementSettingsGUI
{
    const CMD_SHOW = "showContent";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE_SETTINGS = "saveSettings";
    const CMD_SETTINGS = "showSettings";
    const CMD_EDIT_PROPERTIES = "editProperties";
    const CMD_DOWNLOAD_FILE = "downloadFile";

    const F_TITLE = "title";
    const F_DESCRIPTION = "description";
    const F_EMAIL = "email";
    const F_IS_ONLINE = "is_online";
    const F_SHOW_IN_COCKPIT = "show_in_cockpit";
    const F_FILE_UPLOAD = "file_upload";
    const F_DELETE_FILE = "file_upload_delete";

    private static $ALLOWED_MIME_TYPES = array(
        "application/pdf"
    );

    /**
     * @var ilObjWBDManagement
     */
    protected $object;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var FileStorage
     */
    protected $file_storage;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var string
     */
    protected $cancel_link;

    public function __construct(
        ilObjWBDManagement $object,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        Closure $txt,
        FileStorage $file_storage,
        Factory $ui_factory,
        Renderer $renderer,
        string $cancel_link
    ) {
        $this->object = $object;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->file_storage = $file_storage;
        $this->ui_factory = $ui_factory;
        $this->renderer = $renderer;
        $this->cancel_link = $cancel_link;
    }

    /**
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd == null) {
            $cmd = self::CMD_SHOW;
        }

        switch ($cmd) {
            case self::CMD_SHOW:
            case self::CMD_SETTINGS:
            case self::CMD_EDIT_PROPERTIES:
                $this->showSettings();
                break;
            case self::CMD_SAVE_SETTINGS:
                $this->saveSettings();
                break;
            case self::CMD_CANCEL:
                $this->cancelSettings();
                break;
            case self::CMD_DOWNLOAD_FILE:
                $this->downloadFile();
                break;
            default:
                throw new Exception(__METHOD__ . " unknown command " . $cmd);
        }
    }

    protected function showSettings(ilPropertyFormGUI $form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
            $this->fillForm($form);
        }
        $this->tpl->setContent($form->getHtml());
    }

    protected function saveSettings()
    {
        $post = $_POST;
        $files = $_FILES;

        $form = $this->getForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showSettings($form);

            return;
        }

        $title = htmlentities($post[self::F_TITLE]);
        $description = htmlentities($post[self::F_DESCRIPTION]);
        $email = htmlentities($post[self::F_EMAIL]);
        $show_in_cockpit = (bool) $post[self::F_SHOW_IN_COCKPIT];
        $is_online = (bool) $post[self::F_IS_ONLINE];

        if (isset($post[self::F_DELETE_FILE])) {
            $this->file_storage->deleteCurrentFile();
        }

        if ($files[self::F_FILE_UPLOAD]["tmp_name"] == "" && isset($post[self::F_DELETE_FILE])) {
            $file = null;
        } elseif ($files[self::F_FILE_UPLOAD]["tmp_name"] == "" && !isset($post[self::F_DELETE_FILE])) {
            $file = $this->object->getSettings()->getDocumentPath();
        } else {
            $file = $this->importFile($files[self::F_FILE_UPLOAD]);
        }

        if ($this->object->getSettings()->isShowInCockpit() && !$show_in_cockpit) {
            $this->object->deleteProvider();
        }

        if (!$this->object->getSettings()->isShowInCockpit() && $show_in_cockpit) {
            $this->object->createProvider();
        }

        $fnc = function (WBDManagement $s) use ($show_in_cockpit, $is_online, $file, $email) {
            $s = $s
                ->withShowInCockpit($show_in_cockpit)
                ->withOnline($is_online)
                ->withDocumentPath($file)
                ->withEmail($email)
            ;
            return $s;
        };

        $obj = $this->object;
        $obj->setTitle($title);
        $obj->setDescription($description);
        $obj->updateSettings($fnc);
        $obj->update();

        ilUtil::sendSuccess($this->txt("settings_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function downloadFile()
    {
        ilUtil::deliverFile(
            $this->object->getSettings()->getDocumentPath(),
            basename($this->object->getSettings()->getDocumentPath())
        );
    }

    protected function importFile(array $file_info) : string
    {
        if (!in_array($file_info["type"], self::$ALLOWED_MIME_TYPES)) {
            ilUtil::sendInfo($this->txt("wrong_file_type"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }

        if (!$this->file_storage->isEmpty()) {
            $this->file_storage->deleteCurrentFile();
        }

        if (!$this->file_storage->uploadFile($file_info)) {
            ilUtil::sendFailure("file_could_not_be_uploaded", true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }

        return $this->file_storage->getFilePath();
    }

    protected function cancelSettings()
    {
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $f = $this->ui_factory;
        $r = $this->renderer;
        $obj = $this->object;
        $download_link = "";

        $path = $obj->getSettings()->getDocumentPath();
        if (!is_null($path) && $path != "") {
            $download = $this->ctrl->getLinkTargetByClass(
                get_class($this),
                self::CMD_DOWNLOAD_FILE,
                "",
                true,
                false
            );

            $download_link = $r->render($f->link()->standard(
                basename($obj->getSettings()->getDocumentPath()),
                $download
            ));
        }

        $arr = [
            self::F_TITLE => html_entity_decode($obj->getTitle()),
            self::F_DESCRIPTION => html_entity_decode($obj->getDescription()),
            self::F_EMAIL => html_entity_decode($obj->getSettings()->getEmail()),
            self::F_SHOW_IN_COCKPIT => $obj->getSettings()->isShowInCockpit(),
            self::F_IS_ONLINE => $obj->getSettings()->isOnline(),
            self::F_FILE_UPLOAD => $download_link
        ];

        $form->setValuesByArray($arr);
    }

    protected function getForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("settings"));
        $form->setShowTopButtons(true);
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->txt("save"));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ta = new ilTextAreaInputGUI($this->txt("description"), self::F_DESCRIPTION);
        $form->addItem($ta);

        $ti = new ilEMailInputGUI($this->txt("email"), self::F_EMAIL);
        $ti->setRequired(true);
        $form->addItem($ti);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("availability"));
        $form->addItem($sh);

        $ci = new ilCheckBoxInputGUI($this->txt("settings_online"), self::F_IS_ONLINE);
        $form->addItem($ci);

        $ci = new ilCheckBoxInputGUI($this->txt("show_in_cockpit"), self::F_SHOW_IN_COCKPIT);
        $form->addItem($ci);

        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("upload"));
        $form->addItem($sh);

        $fu = new ilFileInputGUI($this->txt("file_upload"), self::F_FILE_UPLOAD);
        $fu->setALlowDeletion(true);
        $form->addItem($fu);

        return $form;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
