<?php declare(strict_types=1);

use CaT\Plugins\AgendaItemPool\AgendaItem;
use CaT\Plugins\AgendaItemPool\ilObjectActions;

require_once(__DIR__ . "/class.ilAgendaItemsGUI.php");

class ilAgendaItemImportGUI
{
    use AgendaItem\IDD_GDV_Content;
    use AgendaItem\Helper;

    const CMD_COPY_AGENDA_ITEM = "copyAgendaItem";
    const CMD_COPY_SELECTED_AGENDA_ITEMS = "copySelectedAgendaItems";
    const SELECTED_ID = "selected_id";

    const S_TITLE = "title";
    const S_GOALS = "goals";
    const S_GDV_CONTENT = "gdv_content";
    const S_IDD_CONTENT = "idd_content";
    const S_IDD_RELEVANT = "idd_relevant";
    const S_ACTIVE = "active";

    public function __construct(\ilAgendaItemsGUI $parent, ilObjectActions $object_actions, Closure $txt)
    {
        $this->parent = $parent;
        $this->object_actions = $object_actions;
        $this->txt = $txt;

        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_usr = $DIC->user();
    }

    /**
     * Process incomming commands.
     *
     * @return void
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCMD();

        switch ($cmd) {
            case self::CMD_COPY_AGENDA_ITEM:
                $this->copyAgendaItem();
                break;
            case self::CMD_COPY_SELECTED_AGENDA_ITEMS:
                $this->copySelectedAgendaItems();
                break;
            case ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS:
                $this->g_ctrl->redirect($this->parent, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
                break;
            default:
                throw new \Exception("Unknown command: " . $cmd);
        }
    }

    /**
     * Copy one or more agenda item/s form another pool.
     *
     * @return 	void
     */
    public function copyAgendaItem()
    {
        $get = $_GET;
        $selected_id = (int) $get[self::SELECTED_ID];
        if (is_null($selected_id) || $selected_id == "") {
            \ilUtil::sendInfo($this->txt('select_pool_first'), true);
            $this->g_ctrl->redirect($this, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
        }

        $agenda_items = $this->object_actions->getAllAgendaItemsByPoolId($selected_id);
        $this->renderAgendaItemImportTable($agenda_items, $selected_id);
    }

    /**
     * Render dimension table data.
     *
     * @return 	void
     */
    protected function renderAgendaItemImportTable(array $agenda_items, int $selected_id)
    {
        $table = new AgendaItem\ilAgendaItemImportTableGUI(
            $this,
            self::CMD_COPY_AGENDA_ITEM,
            $this->object_actions,
            $this->txt,
            $selected_id
            );
        $table->setLimit(10000);
        $table->determineLimit();
        $table->determineOffsetAndOrder();
        $dir = $table->getOrderDirection();
        $order_column = $table->getOrderField();
        $agenda_items = $this->sortAgendaItems($agenda_items, $dir, $order_column);
        $table->setData($agenda_items);
        $table->addCommandButton(self::CMD_COPY_SELECTED_AGENDA_ITEMS, $this->txt("copy"));
        $table->addCommandButton(ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS, $this->txt("cancel"));
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Copy the selected AgendaItems.
     *
     * @return 	void
     */
    public function copySelectedAgendaItems()
    {
        $post = $_POST;
        $target_obj_id = ilObject::_lookupObjId((int) $_GET['ref_id']);

        if ($post['row_selector'] == null) {
            $selected_id = $this->validatePoolIdFromGet();
            \ilUtil::sendInfo($this->txt('no_entries_selected'), true);
            $this->copyAgendaItem($selected_id);
            return;
        }

        foreach ($post['row_selector'] as $id) {
            $ai = $this->object_actions->getAgendaItemById((int) $id);

            $new = $this->object_actions->createAgendaItem(
                $ai->getTitle(),
                new \DateTime('now', new \DateTimeZone("Europe/Berlin")),
                (int) $this->g_usr->getId(),
                $target_obj_id,
                $ai->getDescription(),
                $ai->getIsActive(),
                $ai->getIddRelevant(),
                $ai->getIsDeleted(),
                $ai->getIsBlank(),
                $ai->getTrainingTopics(),
                $ai->getGoals(),
                $ai->getGDVLearningContent(),
                $ai->getIDDLearningContent(),
                $ai->getAgendaItemContent()
            );
        }

        \ilUtil::sendSuccess($this->txt('copy_entries'), true);
        $this->g_ctrl->redirect($this->parent, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
    }
}
