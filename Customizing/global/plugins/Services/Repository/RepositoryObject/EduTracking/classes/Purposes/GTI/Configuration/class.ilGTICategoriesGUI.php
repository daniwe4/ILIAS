<?php

declare(strict_types=1);

use CaT\Plugins\EduTracking\Purposes\GTI\Configuration;

class ilGTICategoriesGUI extends TMSTableParentGUI
{
    const CMD_SHOW_CATEGORIES = "show_categories";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE = "save";
    const CMD_ADD_ENTRY = "add_entry";
    const CMD_CONFIRM_DELETE = "confirm_delete";
    const CMD_DELETE = "delete";

    const F_CATEGORIES = "categories";

    /**
     * @var Configuration\ilActions
     */
    protected $actions;

    public function __construct(ilEduTrackingConfigGUI $parent_gui, Configuration\ilActions $actions)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();
        $this->g_toolbar = $DIC->toolbar();

        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
    }

    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_CATEGORIES:
                $this->show();
                break;
            case self::CMD_CANCEL:
                $this->cancel();
                break;
            case self::CMD_SAVE:
                $this->save();
                break;
            case self::CMD_ADD_ENTRY:
                $this->addEntries();
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE:
                $this->delete();
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
                break;
        }
    }

    protected function show()
    {
        $this->renderTable($this->actions->selectCategories());
    }

    protected function cancel()
    {
        $this->g_ctrl->redirect($this, self::CMD_SHOW_CATEGORIES);
    }

    protected function save()
    {
        $new_categories = $this->getPostEntries();

        if (count($new_categories) == 0) {
            ilUtil::sendInfo($this->txt("nothing_to_save"));
            $this->g_ctrl->redirect($this, self::CMD_SHOW_CATEGORIES);
        }

        $this->actions->insertCategories($new_categories, (int) $this->g_user->getId());

        ilUtil::sendSuccess($this->txt("categories_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW_CATEGORIES);
    }

    protected function confirmDelete()
    {
        $post = $_POST;

        if (!isset($post['check'])) {
            \ilUtil::sendInfo($this->txt('no_entries_delete'), true);
            $this->g_ctrl->redirect($this, self::CMD_SHOW_CATEGORIES);
        }

        $confirmation = new ilConfirmationGUI();

        foreach ($post['check'] as $key => $value) {
            $confirmation->addHiddenItem('check[]', $value);
            $confirmation->addItem('item[]', "", $this->actions->getTitleById((int) $value));
        }
        foreach ($post['hidden_id'] as $key => $value) {
            $confirmation->addHiddenItem('hidden_id[]', $value);
        }

        $confirmation->setFormAction($this->g_ctrl->getFormAction($this));
        $confirmation->setHeaderText($this->txt("delete_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_DELETE);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
        $this->g_tpl->setContent($confirmation->getHTML());
    }

    protected function delete()
    {
        $post = $_POST;
        $this->actions->deleteCategories($post['check']);

        ilUtil::sendSuccess($this->txt("delete_categories"), true);
        $this->g_ctrl->redirect($this, self::CMD_SHOW_CATEGORIES);
    }

    /**
     * @param CategoryGTI[] $data
     */
    protected function renderTable(array $data)
    {
        $this->setToolbar();
        $table = $this->getTMSTableGUI();
        $this->configurateTable($table);
        $data = $this->sortData($data);
        $table->setData($data);
        $this->g_tpl->setContent(
            $table->getHtml()
        );
    }

    private function sortData(array $data) : array
    {
        usort($data, function (Configuration\CategoryGTI $a, Configuration\CategoryGTI $b) {
            return strcasecmp($a->getName(), $b->getName());
        });
        return $data;
    }

    /**
     * Create the toolbar to add an editable amount of data rows
     */
    protected function setToolbar()
    {
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_SHOW_CATEGORIES));
        $this->g_toolbar->setCloseFormTag(false);

        $number = new ilTextInputGUI("", "addnum");
        $number->setSize(2);
        $number->setValue(1);
        $this->g_toolbar->addInputItem($number);
        $this->g_toolbar->addFormButton($this->txt("add_entry"), self::CMD_ADD_ENTRY);
    }

    /**
     * Merge all entries to one array and render
     */
    protected function addEntries()
    {
        $post_categories = $this->getPostEntries();
        $new_categories = $this->getNewEntries();

        $entries = array();
        $entries = array_merge($post_categories, $new_categories);

        $this->renderTable($entries);
    }

    /**
     * @return CategoryGTI[]
     */
    protected function getPostEntries() : array
    {
        $post = $_POST;

        $categories = [];
        $post_categories = $post['categories'];
        if(! is_array($post_categories)) {
            return $categories;
        }

        for ($i = 0; $i < count($post_categories); $i++) {
            $categories[] = new Configuration\CategoryGTI((int) $post['hidden_id'][$i], $post_categories[$i]);
        }

        return $categories;
    }

    /**
     * @return CategoryGTI[]
     */
    protected function getNewEntries() : array
    {
        $post = $_POST;
        $num_entries = $post['addnum'];

        $categories = array();
        for ($i = 0; $i < $num_entries; $i++) {
            $categories[] = new Configuration\CategoryGTI(-1, "");
        }

        return $categories;
    }

    protected function configurateTable(ilTMSTableGUI $table)
    {
        $table->setTitle($this->txt("gti_categories"));
        $table->setExternalSegmentation(false);
        $table->setShowRowsSelector(false);
        $table->setRowTemplate("tpl.gti_categories_row.html", $this->actions->getPlugin()->getDirectory());
        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("categorie_name"));
        $table->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $table->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->txt("delete"));
    }

    /**
     * @inheritdoc
     */
    public function fillRow() : Closure
    {
        return function ($table, $data) {
            $table->getTemplate()->setVariable("POST_VAR", "check");
            $table->getTemplate()->setVariable("ID", $data->getId());
            $table->getTemplate()->setVariable("HIDDEN_ID", $data->getId());

            $ti = new ilTextInputGUI("", "categories[]");
            $ti->setValue($data->getName());
            $table->getTemplate()->setVariable("CATEGORIE_NAME", $ti->render());
        };
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand() : string
    {
        return self::CMD_SHOW_CATEGORIES;
    }

    /**
     * @inheritdoc
     */
    protected function tableId() : string
    {
        return get_class($this);
    }

    protected function txt(string $code) : string
    {
        return $this->actions->getPlugin()->txt($code);
    }
}
