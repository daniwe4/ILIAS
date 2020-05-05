<?php declare(strict_types=1);

use CaT\Plugins\AgendaItemPool\AgendaItem;
use CaT\Plugins\AgendaItemPool\ilObjectActions;

require_once(__DIR__ . "/class.ilAgendaItemGUI.php");
require_once(__DIR__ . "/class.ilAgendaItemImportGUI.php");

/**
 * Class ilAgendaItemsGUI.
 * Provide the GUI to show a list of all AgendaItem in the pool.
 *
 * @ilCtrl_Calls ilAgendaItemsGUI: ilAgendaItemImportGUI
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilAgendaItemsGUI
{
    use AgendaItem\IDD_GDV_Content;
    use AgendaItem\Helper;

    const CMD_SHOW_AGENDA_ITEMS = "showAgendaItems";
    const CMD_EDIT_AGENDA_ITEM = "editAgendaItem";
    const CMD_CANCEL = "cancel";

    const F_SELECT_TITLE = "selectTitel";
    const F_SELECTED = "selected";

    const S_TITLE = "title";
    const S_GOALS = "goals";
    const S_GDV_CONTENT = "gdv_content";
    const S_IDD_CONTENT = "idd_content";
    const S_IDD_RELEVANT = "idd_relevant";
    const S_ACTIVE = "active";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilTabsGUI
     */
    protected $g_tabs;

    /**
     * @var ilToolbarGUI
     */
    protected $g_toolbar;

    /**
     * @var ilObjAgendaItemPoolGUI
     */
    protected $parent_gui;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * Constructor of the class ilAgendaItemGUI.
     *
     * @return void
     */
    public function __construct(
        ilObjAgendaItemPoolGUI $parent_gui,
        ilObjectActions $object_actions,
        \Closure $txt
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_tabs = $DIC->tabs();
        $this->g_toolbar = $DIC->toolbar();
        $this->g_usr = $DIC->user();
        $this->g_objDefinition = $DIC["objDefinition"];
        $this->g_tree = $DIC->repositoryTree();

        $this->parent_gui = $parent_gui;
        $this->object_actions = $object_actions;
        $this->txt = $txt;
    }

    /**
     * Process incomming commands.
     *
     * @return void
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCMD(self::CMD_SHOW_AGENDA_ITEMS);
        $next_class = $this->g_ctrl->getNextClass();

        switch ($next_class) {
            case "ilagendaitemimportgui":
                $gui = new ilAgendaItemImportGUI($this, $this->object_actions, $this->txt);
                $this->g_ctrl->forwardCommand($gui);
                break;
            default:
                switch ($cmd) {
                    case ilAgendaItemGUI::CMD_ADD_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_SAVE_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_EDIT_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_UPDATE_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_ADD_FREE_TEXT_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_EDIT_FREE_TEXT_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_SAVE_FREE_TEXT_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_UPDATE_FREE_TEXT_AGENDA_ITEM:
                    case ilAgendaItemGUI::CMD_CONFIRM_DELETE:
                    case ilAgendaItemGUI::CMD_DELETE_AGENDA_ITEM:
                        $id = $this->validateIdFromGet();
                        $used_ai_ids = $this->getUsedAiIds();
                        $gui = new ilAgendaItemGUI($this, $this->object_actions, $this->txt, in_array($id, $used_ai_ids));
                        $gui->performCommand($cmd);
                        break;
                    case ilAgendaItemImportGUI::CMD_COPY_AGENDA_ITEM:
                        $post = $_POST;
                        $selected_id = (int) $post['selected'];

                        if (is_null($selected_id) || $selected_id == "") {
                            \ilUtil::sendInfo($this->txt('select_pool_first'), true);
                            $this->g_ctrl->redirect($this, self::CMD_SHOW_AGENDA_ITEMS);
                        }
                        $this->redirectImport($cmd, $selected_id);
                        break;
                    case self::CMD_SHOW_AGENDA_ITEMS:
                    case self::CMD_CANCEL:
                        $this->showAgendaItems();
                        break;
                    default:
                        throw new \Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
     * Redirects to import system
     */
    protected function redirectImport(string $cmd, int $selected_id)
    {
        $this->g_ctrl->setParameterByClass("ilAgendaItemImportGUI", ilAgendaItemImportGUI::SELECTED_ID, $selected_id);
        $link = $this->g_ctrl->getLinkTargetByClass(
            array("ilAgendaItemsGUI", "ilAgendaItemImportGUI"),
            $cmd,
            "",
            false,
            false
        );
        $this->g_ctrl->setParameterByClass("ilAgendaItemImportGUI", ilAgendaItemImportGUI::SELECTED_ID, null);
        \ilUtil::redirect($link);
    }

    /**
     * Show the table of AgendaItems.
     *
     * @return void
     */
    public function showAgendaItems()
    {
        $this->setToolbar();
        $table = $this->getAgendaItemTable();
        $table->determineLimit();
        $table->determineOffsetAndOrder();
        $dir = $table->getOrderDirection();
        $order_column = $table->getOrderField();

        $obj_id = (int) $this->object_actions->getObject()->getId();
        $agenda_items_count = $this->object_actions->getAllAgendaItemsCountByPoolId($obj_id);
        $agenda_items = $this->object_actions->getAllAgendaItemsByPoolId($obj_id, $table->getOffset(), $table->getLimit());
        $agenda_items = $this->sortAgendaItems($agenda_items, $dir, $order_column);
        $table->setMaxCount($agenda_items_count);
        $table->setData($agenda_items);
        $table->addMultiCommand(ilAgendaItemGUI::CMD_CONFIRM_DELETE, $this->txt("delete"));
        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Render dimension table data.
     * @return 	void
     */
    protected function getAgendaItemTable()
    {
        return new AgendaItem\ilAgendaItemTableGUI(
            $this,
            self::CMD_SHOW_AGENDA_ITEMS,
            $this->object_actions,
            $this->txt
            );
    }

    /**
     * Create the Toolbar for table gui.
     *
     * @return 	void
     */
    protected function setToolbar()
    {
        require_once("Services/Form/classes/class.ilSelectInputGUI.php");
        $select = new ilSelectInputGUI(self::F_SELECT_TITLE, self::F_SELECTED);
        $select->setOptions($this->getOptions());
        $this->g_toolbar->setFormAction($this->g_ctrl->getFormActionByClass(ilAgendaItemsGUI::class));
        $this->g_toolbar->setCloseFormTag(true);
        $this->g_toolbar->addFormButton($this->txt("add_item"), ilAgendaItemGUI::CMD_ADD_AGENDA_ITEM);
        $this->g_toolbar->addSeparator();
        $this->g_toolbar->addFormButton($this->txt("free_text"), ilAgendaItemGUI::CMD_ADD_FREE_TEXT_AGENDA_ITEM);
        $this->g_toolbar->addSeparator();
        $this->g_toolbar->addInputItem($select);
        $this->g_toolbar->addFormButton($this->txt("copy_item"), ilAgendaItemImportGUI::CMD_COPY_AGENDA_ITEM);
    }

    /**
     * Get the options for select input.
     *
     * @return array
     */
    public function getOptions() : array
    {
        $arr[''] = $this->txt("pls_select");
        $my_id = $this->object_actions->getObject()->getId();
        $obj_ids = $this->object_actions->getObjIds();
        foreach ($obj_ids as $id) {
            if ($id == $my_id) {
                continue;
            }
            $obj = ilObjectFactory::getInstanceByObjId($id);
            $arr[$obj->getId()] = $obj->getTitle();
        }
        return $arr;
    }
}
