<?php

declare(strict_types = 1);

use CaT\Plugins\CourseMember\SignatureList\ConfigurableList;

/**
 * @ilCtrl_Calls ilConfigurableOverviewGUI: ilConfigurableConfigGUI
 */
class ilConfigurableOverviewGUI extends \TMSTableParentGUI
{
    const GET_TEMPLATE_ID = "template_id";
    const CMD_SHOW = "show";
    const CMD_DELETE = "delete";
    const CMD_CANCEL = "cancel";
    const CMD_MULTI_DELETE = "multi_delete";
    const CMD_SAVE_DEFAULT = "save_default";
    const CMD_CONFIRM_DELETE = "confirmDelete";

    const F_HIDDEN_DELETABLES = "hidden_deletables";
    const FIELD_MULTI_DELETE = "delete";

    const TABLE_ID = "conf_lists" ;

    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilPlugin
     */
    protected $plugin;
    /**
     * @var ConfigurableList\ConfigurableListConfigRepo
     */
    protected $repo;
    /**
     * @var ilConfigurableConfigGUI
     */
    protected $config_gui;

    public function __construct(
        \ilTemplate $tpl,
        \ilCtrl $ctrl,
        \ilLanguage $lng,
        \ilPlugin $plugin,
        ConfigurableList\ConfigurableListConfigRepo $repo,
        \ilConfigurableConfigGUI $config_gui
    ) {
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->plugin = $plugin;
        $this->repo = $repo;
        $this->config_gui = $config_gui;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'ilconfigurableconfiggui':
                $this->ctrl->forwardCommand($this->config_gui->withParentGUI($this));
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW:
                        $this->show();
                        break;
                    case self::CMD_DELETE:
                        $this->delete();
                        break;
                    case self::CMD_MULTI_DELETE:
                        $this->mulitDelete();
                        break;
                    case self::CMD_SAVE_DEFAULT:
                        $this->saveDefault();
                        break;
                    case self::CMD_CONFIRM_DELETE:
                        $this->confirmDelete();
                        break;
                    case self::CMD_CANCEL:
                        $this->cancel();
                        break;
                    default:
                        throw new Exception('unknown cmd ' . $cmd);
                }
        }
    }

    protected function show()
    {
        $table = $this->initTable();
        $this->tpl->setContent(
            $this->creationLinkButton()->render()
            . $table->getHTML()
        );
    }

    protected function delete()
    {
        $id = (int) $_POST[self::GET_TEMPLATE_ID];

        if ($this->repo->inUse($id)) {
            ilUtil::sendInfo($this->plugin->txt("is_in_use"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW);
        }

        $this->repo->delete($id);
        ilUtil::sendSuccess($this->plugin->txt("delete_success"), true);
        $this->ctrl->redirect(
            $this,
            self::CMD_SHOW
        );
    }

    protected function mulitDelete()
    {
        $ids = $_POST[self::FIELD_MULTI_DELETE];

        $not_deleted = [];
        foreach ($ids as $key => $id) {
            $id = (int) $id;
            if ($this->repo->inUse($id)) {
                $not_deleted[] = $id;
                unset($ids[$key]);
                continue;
            }

            $this->repo->delete($id);
        }

        if (count($not_deleted) > 0) {
            ilUtil::sendInfo($this->plugin->txt("some_in_use"), true);
        }

        if (count($ids) > 0) {
            ilUtil::sendSuccess($this->plugin->txt("delete_success"), true);
        }

        $this->ctrl->redirect(
            $this,
            self::CMD_SHOW
        );
    }

    protected function confirmDelete()
    {
        $post = $_POST;
        $get = $_GET;

        $confirmation = new ilConfirmationGUI();

        $id = (int) $get[self::GET_TEMPLATE_ID];
        $ids = $post[self::FIELD_MULTI_DELETE];

        if (!is_null($id) && $id !== 0) {
            $obj = $this->repo->load($id);
            $delete_confirmation_text = $this->plugin->txt("delete_confirmation");
            $confirmation->addItem(self::GET_TEMPLATE_ID, $id, $obj->getName());
            $confirmation->setConfirm($this->plugin->txt("confirm"), self::CMD_DELETE);
        } else {
            if (count($ids) == 0) {
                ilUtil::sendInfo($this->plugin->txt("nothing_to_delete"), true);
                $this->ctrl->redirect(
                    $this,
                    self::CMD_SHOW
                );
            }

            foreach ($ids as $id) {
                $id = (int) $id;
                $obj = $this->repo->load($id);
                $confirmation->addItem(self::FIELD_MULTI_DELETE . '[]', $id, $obj->getName());
            }

            $delete_confirmation_text = $this->plugin->txt("delete_multi_confirmation");
            $confirmation->setConfirm($this->plugin->txt("confirm"), self::CMD_MULTI_DELETE);
        }

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setHeaderText($delete_confirmation_text);
        $confirmation->setCancel($this->plugin->txt("cancel"), self::CMD_CANCEL);

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function cancel()
    {
        $this->ctrl->redirect(
            $this,
            self::CMD_SHOW
        );
    }

    protected function saveDefault()
    {
        $default_id = (int) $_POST[ConfigurableList\DBConfigurableListConfigRepo::FIELD_DEFAULT];
        $this->repo->updateDefault($default_id);

        ilUtil::sendSuccess($this->plugin->txt("save_default_success"), true);
        $this->ctrl->redirect(
            $this,
            self::CMD_SHOW
        );
    }

    protected function creationLinkButton()
    {
        require_once __DIR__ . "/class.ilConfigurableConfigGUI.php";
        $lb = \ilLinkButton::getInstance();
        $lb->setUrl(
            $this->ctrl->getLinkTarget(
                $this->config_gui,
                \ilConfigurableConfigGUI::CMD_REQUEST_CREATE
            )
        );
        $lb->setCaption($this->plugin->txt('create_siglist_template'), false);
        return $lb;
    }

    /**
     * @inheritDoc
     */
    protected function fillRow()
    {
        return function (\ilTMSTableGUI $table, array $set) {
            $tpl = $table->getTemplate();
            $tpl->setVariable(
                "POST_VAR",
                self::FIELD_MULTI_DELETE
            );
            $tpl->setVariable(
                "ID",
                $set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_ID]
            );

            $tpl->setVariable(
                'NAME',
                $set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_NAME]
            );
            $tpl->setVariable(
                'DESCRIPTION',
                (string) $set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_DESCRIPTION]
            );
            $tpl->setVariable(
                'TEMPLATE_ID',
                (string) $set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_MAIL_TEMPLATE]
            );

            $tpl->setVariable(
                'ACTIONS',
                $this->actionMenuFor(
                    $set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_ID]
                )
            );

            $tpl->setVariable(
                "DEFAULT_POST",
                ConfigurableList\DBConfigurableListConfigRepo::FIELD_DEFAULT
            );

            if ($set[ConfigurableList\DBConfigurableListConfigRepo::FIELD_DEFAULT]) {
                $tpl->touchBlock("checked");
            }
        };
    }

    /**
     * @inheritDoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW;
    }

    /**
     * @inheritDoc
     */
    protected function tableId()
    {
        return self::TABLE_ID;
    }

    protected function initTable() : ilTMSTableGUI
    {
        $table = $this->getTMSTableGUI();
        $table->setTitle($this->plugin->txt('siglist_config_table_title'));
        $table->setExternalSorting(false);
        $table->setExternalSegmentation(false);
        $table->setRowTemplate("tpl.siglist_templates_row.html", $this->plugin->getDirectory());
        $table->setData($this->repo->tableData());
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setSelectAllCheckbox(self::FIELD_MULTI_DELETE);

        $table->addColumn('', '');
        $table->addColumn($this->plugin->txt('template_id'));
        $table->addColumn($this->plugin->txt('template_description'));
        $table->addColumn($this->plugin->txt('template_mail_id'));
        $table->addColumn($this->plugin->txt('template_default'));
        $table->addColumn($this->plugin->txt('template_actions'));

        $table->addMultiCommand(
            self::CMD_CONFIRM_DELETE,
            $this->plugin->txt("delete_templates")
        );

        $table->addCommandButton(
            self::CMD_SAVE_DEFAULT,
            $this->plugin->txt("save")
        );

        return $table;
    }

    protected function actionMenuFor(int $id)
    {
        $l = new \ilAdvancedSelectionListGUI();
        $l->setListTitle($this->plugin->txt("please_choose"));

        $this->ctrl->setParameterByClass(
            'ilConfigurableConfigGUI',
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            $id
        );

        $l->addItem(
            $this->plugin->txt('edit'),
            'edit',
            $this->ctrl->getLinkTargetByClass(
                'ilConfigurableConfigGUI',
                ilConfigurableConfigGUI::CMD_SHOW
            )
        );
        $this->ctrl->setParameterByClass(
            'ilConfigurableConfigGUI',
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            null
        );
        $this->ctrl->setParameterByClass(
            'ilConfigurableConfigGUI',
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            $id
        );
        $this->ctrl->setParameterByClass(
            'ilConfigurableConfigGUI',
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            null
        );

        $this->ctrl->setParameter(
            $this,
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            $id
        );
        $l->addItem(
            $this->plugin->txt('delete_template'),
            'remove',
            $this->ctrl->getLinkTarget(
                $this,
                self::CMD_CONFIRM_DELETE
            )
        );
        $this->ctrl->setParameter(
            $this,
            ilConfigurableConfigGUI::GET_TEMPLATE_ID,
            null
        );

        $l->setId("selection_list_" . $id);

        return $l->getHTML();
    }
}
