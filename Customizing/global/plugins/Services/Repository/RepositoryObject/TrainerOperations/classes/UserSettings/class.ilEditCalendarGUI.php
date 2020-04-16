<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\UserSettings\CalendarSettings;
use CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar\CalendarRepository;

/**
 * GUI to edit/add a calendar
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilEditCalendarGUI
{
    const CMD_ADD = 'add_cal';
    const CMD_EDIT = 'edit_cal';
    const CMD_SAVE = 'save_cal';
    const CMD_CANCEL = 'cancel_edit_cal';

    const F_CALCAT = 'f_category';
    const F_TITLE = 'f_title';
    const F_TYPE = 'f_type';
    const F_URL = 'f_url';
    const F_UID = 'f_uid';
    const F_PWD = 'f_pwd';

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
     * @var CalendarRepository
     */
    protected $il_cal_repo;

    /**
     * @var AccessHelper
     */
    protected $access;

    public function __construct(
        \Closure $txt,
        \ilCtrl $g_ctrl,
        \ilTemplate $g_tpl,
        CalendarRepository $il_cal_repo,
        AccessHelper $access
    ) {
        $this->txt = $txt;
        $this->g_ctrl = $g_ctrl;
        $this->g_tpl = $g_tpl;
        $this->il_cal_repo = $il_cal_repo;
        $this->access = $access;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_ADD:
                $this->add();
                break;
            case self::CMD_EDIT:
                $this->edit();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_CANCEL:
                $this->redirectToCalSettings();
                break;

            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }
    }


    protected function add()
    {
        $form = $this->initForm();
        $this->g_tpl->setContent($form->getHTML());
    }

    protected function edit()
    {
        $form = $this->initForm();
        $cal_cat = (int) $_GET[self::F_CALCAT];
        $this->setValuesFromStorage($cal_cat, $form);
        $this->g_tpl->setContent($form->getHTML());
    }

    protected function save()
    {
        $form = $this->initForm();
        $form->setValuesByPost();
        if (!$form->checkInput()) {
            \ilUtil::sendFailure($this->txt('msg_cal_err_check_input'));
            $this->g_tpl->setContent($form->getHTML());
            return;
        }

        $type = (int) $form->getInput(self::F_TYPE);

        if ($type === $this->il_cal_repo->getTypeGlobal()) { // && may?!
            $cat_obj_id = 0;
        }
        if ($type === $this->il_cal_repo->getTypeUser()) {
            $cat_obj_id = $this->access->getCurrentuserId();
        }

        $cat = $this->il_cal_repo->buildCategory(
            (int) $form->getInput(self::F_CALCAT),
            $form->getInput(self::F_TITLE),
            $type,
            $cat_obj_id,
            $form->getInput(self::F_URL),
            $form->getInput(self::F_UID),
            $form->getInput(self::F_PWD)
        );

        $this->il_cal_repo->storeCategory($cat);
        //$this->il_cal_repo->synchronize($cat);

        \ilUtil::sendSuccess($this->txt('msg_cal_saved'), true);
        $this->redirectToCalSettings();
    }

    protected function redirectToCalSettings()
    {
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainerOperationsGUI", "ilTrainerOperationsCalSettingsGUI"),
            ilTrainerOperationsCalSettingsGUI::CMD_SHOW,
            "",
            false,
            false
        );

        \ilUtil::redirect($link);
    }

    protected function initForm() : \ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->txt('edit_cal_title'));
        $form->setFormAction($this->g_ctrl->getFormAction($this));
        $form->addCommandButton(self::CMD_SAVE, $this->txt('save'));
        $form->addCommandButton(self::CMD_CANCEL, $this->txt('cancel'));

        $hidden = new \ilHiddenInputGUI(self::F_CALCAT);
        $hidden->setValue(-1);
        $form->addItem($hidden);

        $title = new \ilTextInputGUI($this->txt('cal_calendar_name'), self::F_TITLE);
        $title->setRequired(true);
        $title->setMaxLength(64);
        $title->setSize(32);
        $form->addItem($title);

        $type = new \ilRadioGroupInputGUI($this->txt('cal_cal_type'), self::F_TYPE);
        $type->setValue($this->il_cal_repo->getTypeUser());
        $opt = new \ilRadioOption($this->txt('cal_type_personal'), $this->il_cal_repo->getTypeUser());
        $type->addOption($opt);

        if ($this->access->mayEditGeneralCalendars()) {
            $opt = new \ilRadioOption($this->txt('cal_type_general'), $this->il_cal_repo->getTypeGlobal());
            $type->addOption($opt);
        }

        $type->setRequired(true);
        $type->setInfo($this->txt('cal_type_info'));
        $form->addItem($type);

        $url = new \ilTextInputGUI($this->txt('cal_remote_url'), self::F_URL);
        $url->setMaxLength(500);
        $url->setSize(60);
        $url->setRequired(true);
        $form->addItem($url);

        $user = new \ilTextInputGUI($this->txt('username'), self::F_UID);
        $user->setMaxLength(50);
        $user->setSize(20);
        $user->setRequired(false);
        $form->addItem($user);

        $pass = new \ilPasswordInputGUI($this->txt('password'), self::F_PWD);
        $pass->setMaxLength(50);
        $pass->setSize(20);
        $pass->setRetype(false);
        $pass->setInfo($this->txt('remote_pass_info'));
        $form->addItem($pass);

        return $form;
    }

    protected function setValuesFromStorage(int $cal_cat, \ilPropertyFormGUI $form)
    {
        $category = $this->il_cal_repo->getCategoryById($cal_cat);
        $values = [
            self::F_CALCAT => $cal_cat,
            self::F_TITLE => $category->getTitle(),
            self::F_TYPE => $category->getType(),
            self::F_URL => $category->getRemoteUrl(),
            self::F_UID => $category->getRemoteUser(),
            self::F_PWD => $category->getRemotePass()
        ];

        $form->setValuesByArray($values);
    }


    public function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
