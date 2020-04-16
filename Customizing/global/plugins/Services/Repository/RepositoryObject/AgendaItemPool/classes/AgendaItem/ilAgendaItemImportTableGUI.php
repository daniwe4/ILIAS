<?php declare(strict_types=1);

namespace CaT\Plugins\AgendaItemPool\AgendaItem;

require_once("Services/Table/classes/class.ilTable2GUI.php");

use CaT\Plugins\AgendaItemPool\ilObjectActions;

/**
 * Table for AgendaItems.
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ilAgendaItemImportTableGUI extends \ilTable2GUI
{
    use IDD_GDV_Content;

    const POST_VAR = "row_selector";

    /**
     * @var ilControl
     */
    protected $g_ctrl;

    /**
     * @var ilObjUser
     */
    protected $g_usr;

    /**
     * @var ilAgendaItemGUI
     */
    protected $parent_gui;

    /**
     * @var ilObjectActions
     */
    protected $object_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var ilPluginActions
     */
    protected $cc_actions;

    /**
     * Constructor of the class ilAgendaItemTableGUI.
     *
     * @param 	ilAgendaItemsGUI 	$parent_gui
     * @param 	ilObjectActions 	$object_actions
     * @param 	string 				$parent_command
     * @param 	\Closure 			$txt
     * @return 	void
     */
    public function __construct(
        \ilAgendaItemImportGUI 	$parent_gui,
        string					$parent_command,
        ilObjectActions 	$object_actions,
        \Closure			$txt,
        int					$pool_id
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_usr = $DIC->user();

        $this->parent_gui = $parent_gui;
        $this->object_actions = $object_actions;
        $this->txt = $txt;
        $this->pool_id = $pool_id;

        $this->setId("agenda_items");
        parent::__construct($parent_gui, $parent_command);

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("import_agenda_item_pool"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.table_agenda_items_import_row.html", $this->object_actions->getPluginDirectory());
        $this->g_ctrl->setParameter($this->parent_gui, "pool_id", $id);
        $this->setFormAction($this->g_ctrl->getFormAction($this->parent_gui));
        $this->g_ctrl->clearParameters($this->parent_gui);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setEnableAllCommand(true);
        $this->setShowRowsSelector(false);
        $this->setDefaultOrderDirection("asc");
        $this->setDefaultOrderField(\ilAgendaItemsGUI::S_TITLE);
        $this->setSelectAllCheckbox(self::POST_VAR . "[]");

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt('title'), \ilAgendaItemsGUI::S_TITLE);
        $this->addColumn($this->txt('descripiton'));
        $this->addColumn($this->txt('is_active'), \ilAgendaItemsGUI::S_ACTIVE);
        $this->addColumn($this->txt('agenda_item_content'));
        $this->addColumn($this->txt('goals'), \ilAgendaItemsGUI::S_GOALS);
        $this->addColumn($this->txt('topic'));

        if ($this->object_actions->isEduTrackingActive()) {
            $this->addColumn($this->txt('gdv_learning_content'), \ilAgendaItemsGUI::S_GDV_CONTENT);
            $this->addColumn($this->txt('idd_learning_content'), \ilAgendaItemsGUI::S_IDD_CONTENT);
            $this->addColumn($this->txt('idd_relevant'), \ilAgendaItemsGUI::S_IDD_RELEVANT);
        }

        $this->addColumn($this->txt('last_change'));
        $this->addColumn($this->txt('change_usr'));
    }

    /**
     * @inheritdoc
     */
    public function fillRow($set)
    {
        $topics = $this->getCCActions()->getTopicsNames($set->getTrainingTopics());

        $this->tpl->setVariable("POST_VAR", self::POST_VAR);
        $this->tpl->setVariable("ID", $set->getObjId());
        $this->tpl->setVariable("TITLE", $set->getTitle());
        $this->tpl->setVariable("DESCRIPTION", $this->getValueOrDefault($set->getDescription()));
        $this->tpl->setVariable("IS_ACTIVE", $this->boolToString($set->getIsActive()));
        $this->tpl->setVariable("AGENDA_ITEM_CONTENT", $this->getValueOrDefault($set->getAgendaItemContent()));
        $this->tpl->setVariable("GOALS", $this->getValueOrDefault($set->getGoals()));
        $this->tpl->setVariable("TOPIC", implode("<br />", $topics));

        if ($this->object_actions->isEduTrackingActive()) {
            $this->tpl->setCurrentBlock("gdv_learning_content");
            $this->tpl->setVariable("GDV_LEARNING_CONTENT", $this->getArrayValueOrDefault(self::$gdv_content, $set->getGDVLearningContent()));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("idd_learning_content");
            $this->tpl->setVariable("IDD_LEARNING_CONTENT", $this->getArrayValueOrDefault(self::$idd_content, $set->getIDDLearningContent()));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("idd_relevant");
            $this->tpl->setVariable("IDD_RELEVANT", $this->boolToString($set->getIddRelevant()));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("LAST_CHANGE", $set->getLastChange()->format("Y-m-d H:i:s"));
        $this->tpl->setVariable("CHANGE_USR", $this->g_usr->_lookupFullname($set->getChangeUsrId()));
    }

    /**
     * Turn bool into a readable string.
     */
    protected function boolToString(bool $bool) : string
    {
        if ($bool) {
            return $this->txt("yes");
        }
        return $this->txt("no");
    }

    /**
     * Switch empty string to minus.
     */
    protected function getValueOrDefault(string $value, string $default = "-") : string
    {
        if ($value === "" || is_null($value)) {
            return $default;
        }
        return $value;
    }

    /**
     * Get the value of array with given key or default.
     */
    protected function getArrayValueOrDefault(array $src, string $key = null, $default = "-") : string
    {
        if (is_null($key) || !array_key_exists($key, $src)) {
            return $default;
        }
        return $src[$key];
    }

    /**
     * Get the plugin actions of course classification.
     *
     * @return 	ilPluginActions
     */
    protected function getCCActions()
    {
        if ($this->cc_actions == null) {
            $this->cc_actions = $this->object_actions->getObject()->getCourseClassificationActions();
        }
        return $this->cc_actions;
    }

    public function render()
    {
        $this->g_ctrl->setParameter($this->parent_gui, "pool_id", $this->pool_id);
        return parent::render();
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
