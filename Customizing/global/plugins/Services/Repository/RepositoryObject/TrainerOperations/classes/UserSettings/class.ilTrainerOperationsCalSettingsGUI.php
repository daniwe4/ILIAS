<?php

declare(strict_types=1);

use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\UserSettings\CalSettingsTableGUI;
use CaT\Plugins\TrainerOperations\UserSettings\CalSettingsRepository;
use CaT\Plugins\TrainerOperations\UserSettings\TEPCalendarSettings;
use CaT\Plugins\TrainerOperations\UserSettings\ilAddCalendarGUI;

/**
 * GUI for users' settings.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilTrainerOperationsCalSettingsGUI
{
    const CMD_SHOW = 'show_cal_settings';
    const CMD_SAVE = 'save_cal_settings';
    const CMD_DELETE = 'delete_cal';

    const F_OBJ_INFO = 'f_obj_info';
    const F_USE = 'f_use';
    const F_HIDE_DETAILS = 'f_hide';
    const F_STORAGE_ID = 'f_storage_id';
    const F_CALCAT_ID = 'f_calcat_id';
    const F_USR_ID = 'f_usr_id';

    /**
     * @var array
     */
    protected $modals = [];

    /**
     * @var AccessHelper
     */
    protected $access;

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
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var CalSettingsTableGUI
     */
    protected $table;

    /**
     * @var CalSettingsRepository
     */
    protected $db;

    /**
     * @var int
     */
    protected $tep_obj_id;

    public function __construct(
        AccessHelper $access,
        \Closure $txt,
        \ilCtrl $g_ctrl,
        \ilTemplate $g_tpl,
        \ilToolbarGUI $toolbar,
        \ILIAS\UI\Implementation\Factory $ui_factory,
        \ILIAS\UI\Implementation\DefaultRenderer $ui_renderer,
        CalSettingsRepository $db,
        int $tep_obj_id

    ) {
        $this->access = $access;
        $this->txt = $txt;
        $this->g_ctrl = $g_ctrl;
        $this->g_tpl = $g_tpl;
        $this->toolbar = $toolbar;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->db = $db;
        $this->tep_obj_id = $tep_obj_id;
    }

    public function setTableGUI(CalSettingsTableGUI $table_gui)
    {
        $this->table = $table_gui;
    }


    public function executeCommand()
    {
        if (!(
            $this->access->mayEditOwnCalendars()
            || $this->access->mayEditGeneralCalendars()
        )) {
            $this->access->redirectInfo('disallowed_usersettings');
        }

        $cmd = $this->g_ctrl->getCmd();

        if (!$cmd) {
            $cmd = self::CMD_SHOW;
        }
        switch ($cmd) {
            case self::CMD_SHOW:
                $this->initToolbar();
                $this->showCalSettingsTable();
                break;
            case self::CMD_SAVE:
                $post_values = $this->digestPost($_POST);
                $this->saveCalSettingsTable($post_values);
                break;
            case self::CMD_DELETE:
                $cal_catid = $this->digestDeletePost($_POST);
                $this->delete($cal_catid);
                break;

            default:
                throw new Exception(__METHOD__ . " :: Unknown command " . $cmd);
        }
    }

    protected function initToolbar()
    {
        $url = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainerOperationsGUI", "ilEditCalendarGUI"),
            \ilEditCalendarGUI::CMD_ADD
        );
        $btn = $this->ui_factory->button()->standard($this->txt('add_calendar'), $url);
        $this->toolbar->addComponent($btn);
    }


    protected function showCalSettingsTable()
    {
        $data = $this->db->getPossibleSettingsFor($this->tep_obj_id);

        $this->table->setData($data);
        $this->table->setFormAction($this->g_ctrl->getFormAction($this));
        $this->table->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $content = $this->table->getHtml();

        if (count($this->modals) > 0) {
            $content .= $this->ui_renderer->render($this->modals);
        }

        $this->g_tpl->setContent($content);
    }

    protected function delete(int $cal_catid)
    {
        $this->db->deleteCalendar($cal_catid);
        \ilUtil::sendSuccess($this->txt("delete_calendar_success"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW);
    }

    public function getActions(int $cal_catid) : string
    {
        $edit_link = $this->getEditLink($cal_catid);
        $modal = $this->getModal($cal_catid);
        $this->modals[] = $modal;

        $items = array(
            $this->ui_factory->button()->shy($this->txt('edit_calendar'), $edit_link),
            $this->ui_factory->button()->shy($this->txt('delete_calendar'), '')->withOnClick($modal->getShowSignal())
        );

        return $this->ui_renderer->render([$this->ui_factory->dropdown()->standard($items)->withLabel($this->txt("actions"))]);
    }

    protected function getEditLink(int $cal_catid) : string
    {
        $url = $this->g_ctrl->getLinkTargetByClass(
            array("ilObjTrainerOperationsGUI", "ilEditCalendarGUI"),
            \ilEditCalendarGUI::CMD_EDIT
        );
        $url .= '&' . \ilEditCalendarGUI::F_CALCAT . '=' . $cal_catid; //TODO: this is not nice
        return $url;
    }

    protected function getModal(int $cal_catid)
    {
        $category = $this->db->getCategory($cal_catid);
        $icon = $this->ui_factory->image()->standard('./templates/default/images/icon_xtep.svg', '');
        $modal = $this->ui_factory->modal()->interruptive(
            $this->txt("modal_delete_title"),
            $this->txt("modal_delete_text"),
            $this->getDeleteLink()
        )->withAffectedItems(
            array($this->ui_factory->modal()->interruptiveItem($cal_catid, $category->getTitle(), $icon, ""))
        );
        return $modal;
    }

    protected function getDeleteLink() : string
    {
        $url = $this->g_ctrl->getLinkTargetByClass(
            array(self::class),
            self::CMD_DELETE
        );
        return $url;
    }

    /**
     * @return TEPCalendarSettings[]
     */
    protected function digestPost(array $post) : array
    {
        if (!$post[self::F_USE]) {
            $post[self::F_USE] = [];
        }
        if (!$post[self::F_HIDE_DETAILS]) {
            $post[self::F_HIDE_DETAILS] = [];
        }

        $post_values = [];
        foreach ($post[self::F_OBJ_INFO] as $info) {
            $info = json_decode(html_entity_decode($info), true);
            $use = false;
            $hide = false;
            if (in_array($info['counter'], $post[self::F_USE])) {
                $use = true;
            }
            if (in_array($info['counter'], $post[self::F_HIDE_DETAILS])) {
                $hide = true;
            }

            $set = new TEPCalendarSettings(
                $info[self::F_STORAGE_ID],
                $this->tep_obj_id,
                $info[self::F_CALCAT_ID],
                $info[self::F_USR_ID],
                $use,
                $hide
            );
            $post_values[] = $set;
        }

        return $post_values;
    }

    protected function digestDeletePost(array $post) : int
    {
        return (int) array_shift($post["interruptive_items"]);
    }

    /**
     * @param TEPCalendarSettings[] $tep_cal_settings
     */
    protected function saveCalSettingsTable(array $tep_cal_settings)
    {
        $this->db->updateTepUserSettings($tep_cal_settings);
        \ilUtil::sendSuccess($this->txt("save_settings_success"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW);
    }

    public function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
