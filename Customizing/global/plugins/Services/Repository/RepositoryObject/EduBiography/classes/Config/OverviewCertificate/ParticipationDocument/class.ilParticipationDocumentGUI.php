<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Config\OverviewCertificate\ParticipationDocument;

class ilParticipationDocumentGUI
{
    const CMD_SHOW_CONFIG = "showConfig";
    const CMD_UPLOAD_IMAGE = "uploadImage";

    const IMAGE = "image";
    const IMAGE_DELETE = "image_delete";
    const IMAGE_SIZE = "size";

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
     * @var ParticipationDocument\ilFileStorage
     */
    protected $file_storage;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        Closure $txt,
        ParticipationDocument\ilFileStorage $file_storage
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->file_storage = $file_storage;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SHOW_CONFIG:
                $this->showConfig();
                break;
            case self::CMD_UPLOAD_IMAGE:
                $this->uploadImage();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showConfig(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function uploadImage()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showConfig($form);
            return;
        }

        $post = $_POST;
        $fileData = $post[self::IMAGE];

        if ($post[self::IMAGE_DELETE] || $fileData[self::IMAGE_SIZE] > 0) {
            $this->file_storage->deleteFiles();
        }

        if ($fileData[self::IMAGE_SIZE] > 0) {
            $this->file_storage->upload($fileData);
        }

        ilUtil::sendSuccess($this->txt("part_document_image_uploaded"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_CONFIG);
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("part_document_image"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $im = new ilImageFileInputGUI($this->txt("part_document_logo"), self::IMAGE);
        $im->setInfo($this->txt("part_document_logo_info"));
        $form->addItem($im);

        $form->addCommandButton(self::CMD_UPLOAD_IMAGE, $this->txt("part_document_upload"));
        $form->addCommandButton(self::CMD_SHOW_CONFIG, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $values = [
            self::IMAGE => $this->file_storage->getImageName()
        ];
        /** @var ilImageFileInputGUI $im */
        $im = $form->getItemByPostVar(self::IMAGE);
        $im->setImage($this->file_storage->getIncludePath());

        $form->setValuesByArray($values);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
