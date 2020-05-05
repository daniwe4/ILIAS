<?php

declare(strict_types=1);

use CaT\Plugins\CourseCreation\Recipients\DB;

class ilFailedCreationRecipientsGUI
{
    const CMD_SHOW = "showReciptient";
    const CMD_SAVE = "saveRecipients";
    const CMD_AUTOCOMPLETE = "userfieldAutocomplete";

    const F_LOGIN = "login";

    protected $ctrl;
    protected $tpl;
    protected $txt;
    protected $db;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        Closure $txt,
        DB $db
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->db = $db;
    }

    /**
     * @inheritDoc
     * @throws Exception if cmd is not known
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
                $this->showReciptient();
                break;
            case self::CMD_SAVE:
                $this->saveRecipients();
                break;
            case self::CMD_AUTOCOMPLETE:
                $this->userfieldAutocomplete();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showReciptient(ilPropertyFormGUI $form = null)
    {
        if (is_null($form)) {
            $form = $this->initForm();
            $this->fillForm($form);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function saveRecipients()
    {
        $form = $this->initForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showReciptient($form);
            return;
        }

        $post = $_POST;
        $recipients = $post[self::F_LOGIN];
        $this->db->saveRecipients($recipients);

        ilUtil::sendSuccess($this->txt("recipients_saved"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW);
    }

    protected function userfieldAutocomplete()
    {
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login','firstname','lastname','email'));
        $auto->enableFieldSearchableCheck(false);
        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        echo $auto->getList($_REQUEST['term']);
        exit();
    }

    protected function initForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->txt("recipients"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        $ti = new ilTextInputGUI($this->txt("login"), self::F_LOGIN);
        $ti->setInfo($this->txt("login_info"));
        $ti->setMulti(true);
        $ti->setDataSource($this->ctrl->getLinkTarget($this, self::CMD_AUTOCOMPLETE, "", true, false));
        $form->addItem($ti);

        $form->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $this->txt("cancel"));

        return $form;
    }

    protected function fillForm(ilPropertyFormGUI $form)
    {
        $val = [
            self::F_LOGIN => $this->db->getRecipientsForForm()
        ];
        $form->setValuesByArray($val);
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
