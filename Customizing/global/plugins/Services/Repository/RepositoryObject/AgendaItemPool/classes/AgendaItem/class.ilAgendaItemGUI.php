<?php declare(strict_types=1);

use CaT\Plugins\AgendaItemPool\ilObjectActions;
use CaT\Plugins\AgendaItemPool\AgendaItem;

/**
 * Class ilAgendaItemGUI.
 * Provides the form to create a new AgendaItem.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilAgendaItemGUI
{
    use AgendaItem\IDD_GDV_Content;
    use AgendaItem\Helper;

    const F_OBJ_ID = "obj_id";
    const F_TITLE = "title";
    const F_DESCRIPTION = "descripiton";
    const F_IS_ACTIVE = "is_active";
    const F_IDD_RELEVANT = "idd_relevant";
    const F_IS_DELETED = "is_deleted";
    const F_LAST_CHANGE = "last_change";
    const F_CHANGE_USER_ID = "change_user";
    const F_POOL_ID = "pool_id";
    const F_IS_BLANK = "is_blank";
    const F_TOPICS = "topics";
    const F_GOALS = "goals";
    const F_GDV_LEARNING_CONTENT = "gdv_learning_content";
    const F_IDD_LEARNING_CONTENT = "idd_learning_content";
    const F_AGENDA_ITEM_CONTENT = "agenda_item_content";

    const CMD_ADD_AGENDA_ITEM = "addAgendaItem";
    const CMD_SAVE_AGENDA_ITEM = "saveAgendaItem";
    const CMD_EDIT_AGENDA_ITEM = "editAgendaItem";
    const CMD_UPDATE_AGENDA_ITEM = "updateAgendaItem";
    const CMD_ADD_FREE_TEXT_AGENDA_ITEM = "addFreeTextAgendaItem";
    const CMD_EDIT_FREE_TEXT_AGENDA_ITEM = "editFreeTextAgendaItem";
    const CMD_UPDATE_FREE_TEXT_AGENDA_ITEM = "updateFreeTextAgendaItem";
    const CMD_SAVE_FREE_TEXT_AGENDA_ITEM = "saveFreeTextAgendaItem";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_DELETE_AGENDA_ITEM = "deleteAgendaItems";
    const CMD_CANCEL = "cancel";

    const TIMEZONE = "Europe/Berlin";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilObjUser
     */
    protected $g_usr;

    /**
     * @var ilDimensionsGUI
     */
    protected $parent_gui;

    /**
     * @var ScaledFeedback\ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var bool
     */
    protected $free_text;

    /**
     * @var bool
     */
    protected $used;

    /**
     * Constructor of the class ilAgendaItemGUI.
     *
     * @param 	ilAgendaItemsGUI				$parent_gui
     * @param 	ilObjectActions	$object_actions
     * @param 	\Closure						$txt
     * @param 	bool 							$free_text
     * @param 	int[]							$used_pool_item_ids
     */
    public function __construct(
        ilAgendaItemsGUI $parent_gui,
        ilObjectActions $object_actions,
        \Closure $txt,
        bool $used
    ) {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_user = $DIC->user();
        $this->g_objDefinition = $DIC["objDefinition"];
        $this->g_tree = $DIC->repositoryTree();

        $this->parent_gui = $parent_gui;
        $this->object_actions = $object_actions;
        $this->txt = $txt;

        $this->used = $used;
    }

    /**
     * Process incomming commands.
     *
     * @throws 	Exception
     * @return 	void
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case self::CMD_ADD_AGENDA_ITEM:
                $this->addAgendaItem();
                break;
            case self::CMD_SAVE_AGENDA_ITEM:
                $this->saveAgendaItem();
                break;
            case self::CMD_EDIT_AGENDA_ITEM:
                $this->editAgendaItem();
                break;
            case self::CMD_UPDATE_AGENDA_ITEM:
                $this->updateAgendaItem();
                break;
            case self::CMD_ADD_FREE_TEXT_AGENDA_ITEM:
                $this->addAgendaItem(true);
                break;
            case self::CMD_EDIT_FREE_TEXT_AGENDA_ITEM:
                $this->editAgendaItem(true);
                break;
            case self::CMD_SAVE_FREE_TEXT_AGENDA_ITEM:
                $this->saveAgendaItem(true);
                break;
            case self::CMD_UPDATE_FREE_TEXT_AGENDA_ITEM:
                $this->updateAgendaItem(true);
                break;
            case self::CMD_CONFIRM_DELETE:
                $this->confirmDelete();
                break;
            case self::CMD_DELETE_AGENDA_ITEM:
                $this->deleteAgendaItems();
                break;
            case self::CMD_CANCEL:
                $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
                break;
            default:
                throw new Exception("Unknown command " . $cmd);
        }
    }

    /**
     * Add the form to the global tpl to show the content.
     *
     * @return 	void
     */
    protected function showContent(\ilPropertyFormGUI $form = null)
    {
        if ($form == null) {
            $form = $this->getForm();
        }
        $this->g_tpl->setContent($form->getHtml());
    }

    /**
     * Add an agenda item.
     *
     * @return 	void
     */
    public function addAgendaItem(bool $free_text = false)
    {
        $form = $this->getForm($free_text);
        $this->showContent($form);
    }

    /**
     * Edit AgendaItem.
     *
     * @return 	void
     */
    public function editAgendaItem(bool $free_text = false)
    {
        $form = $this->getForm($free_text, true);

        $this->fillForm($form, $this->getAgendaItem(), $free_text);
        $this->showContent($form);
    }

    /**
     * Save an agenda item to db.
     *
     * @return 	void
     */
    protected function saveAgendaItem(bool $free_text = false)
    {
        $form = $this->getForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showContent($form);
            return;
        }

        $post = $_POST;
        $title = $post[self::F_TITLE];
        $last_change = new \DateTime('now', new \DateTimeZone(self::TIMEZONE));
        $change_usr_id = (int) $this->g_user->getId();
        $pool_id = (int) $this->object_actions->getObject()->getId();
        $description = $post[self::F_DESCRIPTION];
        $is_active = (bool) $post[self::F_IS_ACTIVE];
        $idd_relevant = false;
        $is_deleted = false;
        $training_topics = $this->toIntArray($post[self::F_TOPICS]);
        $goals = "";
        $gdv_learning_content = "";
        $idd_learning_content = "";
        $agenda_item_content = "";

        if ($this->object_actions->isEduTrackingActive()) {
            $gdv_learning_content = $post[self::F_GDV_LEARNING_CONTENT];
            $idd_relevant = (bool) $post[self::F_IDD_RELEVANT];
            $idd_learning_content = $post[self::F_IDD_LEARNING_CONTENT];
        }

        if (!$free_text) {
            $is_deleted = (bool) $post[self::F_IS_DELETED];
            $goals = $post[self::F_GOALS];
            $agenda_item_content = $post[self::F_AGENDA_ITEM_CONTENT];
        }

        // new AgendaItem
        $ai = $this->object_actions->createAgendaItem(
            $title,
            $last_change,
            $change_usr_id,
            $pool_id,
            $description,
            $is_active,
            $idd_relevant,
            $is_deleted,
            $free_text,
            $training_topics,
            $goals,
            $gdv_learning_content,
            $idd_learning_content,
            $agenda_item_content
        );

        \ilUtil::sendSuccess($this->txt("add_successful"), true);
        $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
    }

    /**
     * Update an AgendaItem.
     *
     * @return 	void
     */
    protected function updateAgendaItem(bool $free_text = false)
    {
        $form = $this->getForm();
        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $this->showContent($form);
            return;
        }
        $post = $_POST;

        $ai = $this->getAgendaItem();

        $ai = $ai
            ->withTitle($post[self::F_TITLE])
            ->withDescription($post[self::F_DESCRIPTION])
            ->withIsActive((bool) $post[self::F_IS_ACTIVE])
            ->withTrainingTopics($this->toIntArray($post[self::F_TOPICS]))
            ->withIsBlank($free_text)
            ->withChangeUsrId((int) $this->g_user->getId())
            ->withLastChange(new DateTime());

        if ($this->object_actions->isEduTrackingActive()) {
            $ai = $ai
                ->withGDVLearningContent($post[self::F_GDV_LEARNING_CONTENT])
                ->withIddRelevant((bool) $post[self::F_IDD_RELEVANT])
                ->withIDDLearningContent($post[self::F_IDD_LEARNING_CONTENT]);
        }

        if (!$free_text) {
            $ai = $ai
                ->withIsDeleted((bool) $post[self::F_IS_DELETED])
                ->withGoals($post[self::F_GOALS])
                ->withAgendaItemContent($post[self::F_AGENDA_ITEM_CONTENT]);
        }

        $this->object_actions->updateAgendaItem($ai);

        \ilUtil::sendSuccess($this->txt("update_successful"), true);
        $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
    }

    /**
     * Delete selected agenda items.
     *
     * @return 	void
     */
    public function deleteAgendaItems()
    {
        $items = array_map(function ($a) {
            return (int) $a;
        }, $_POST["delete"]);
        $this->object_actions->deleteAgendaItems($items);
        \ilUtil::sendSuccess($this->txt('entries_delete'), true);
        $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
    }

    /**
     * Handle the confirmDelete command
     *
     * @return 	void
     */
    protected function confirmDelete()
    {
        $post = $_POST;
        $ais = array();
        $id = $this->validateIdFromGet();
        $selected_rows = $post['row_selector'];

        if (!isset($post['row_selector']) && $id == -1) {
            \ilUtil::sendInfo($this->txt('no_entries_delete'), true);
            $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
        }

        if ($id != -1) {
            $ais[] = $this->getAgendaItem();
        } else {
            foreach ($post['row_selector'] as $aid) {
                $ais[] = $this->object_actions->getAgendaItemById((int) $aid);
            }
        }

        $used_ai_ids = $this->getUsedAiIds();
        $delete_ais = array_filter($ais, function ($ai) use ($used_ai_ids) {
            return $this->deletePossible($ai->getObjId(), $used_ai_ids);
        });

        if (count($delete_ais) == 0) {
            \ilUtil::sendInfo($this->txt('no_possible_delete'), true);
            $this->g_ctrl->redirect($this->parent_gui, ilAgendaItemsGUI::CMD_SHOW_AGENDA_ITEMS);
        }

        $no_delete_ais = array_udiff($ais, $delete_ais, array($this, "compare_objects"));
        if (count($no_delete_ais) > 0) {
            $tpl = new \ilTemplate("tpl.no_delete_ais.html", true, true, $this->object_actions->getObject()->getPluginDirectory());
            $tpl->setVariable("TITLE", $this->txt("no_delete_title"));
            foreach ($no_delete_ais as $ai) {
                $tpl->setCurrentBlock("no_delete_ai_title");
                $tpl->setVariable("AI_TITLE", $ai->getTitle());
                $tpl->parseCurrentBlock();
            }

            \ilUtil::sendInfo($tpl->get(), true);
        }

        require_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $confirmation = new ilConfirmationGUI();

        foreach ($delete_ais as $ai) {
            $confirmation->addItem("delete[]", $ai->getObjId(), $ai->getTitle());
        }

        $confirmation->setFormAction($this->g_ctrl->getFormAction($this->parent_gui));
        $confirmation->setHeaderText($this->txt("delete_confirmation"));
        $confirmation->setConfirm($this->txt("confirm"), self::CMD_DELETE_AGENDA_ITEM);
        $confirmation->setCancel($this->txt("cancel"), self::CMD_CANCEL);
        $this->g_tpl->setContent($confirmation->getHTML());
    }

    /**
     * Checks if it's possible to delete the ai
     *
     * @param int[] 	$used_ai_ids
     */
    protected function deletePossible(int $ai_id, array $used_ai_ids) : bool
    {
        return $this->used === false;
    }

    /**
     * Get the form for AgendaItemGUI.
     *
     * @return 	ilPropertyFormGUI
     */
    protected function getForm(bool $free_text = false, bool $update = false)
    {
        require_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
        require_once("Services/Form/classes/class.ilCheckboxInputGUI.php");
        require_once("Services/Form/classes/class.ilSelectInputGUI.php");

        $cc_actions = $this->getCCActions();

        $form = new ilPropertyFormGUI();
        $this->g_ctrl->setParameter($this->parent_gui, "id", $this->validateIdFromGet());
        $form->setFormAction($this->g_ctrl->getFormAction($this->parent_gui));
        $this->g_ctrl->setParameter($this->parent_gui, "id", null);
        $form->setTitle($this->txt("title_build_agenda_item"));
        $form->setShowTopButtons(true);
        if (!$free_text) {
            if ($update) {
                $form->addCommandButton(self::CMD_UPDATE_AGENDA_ITEM, $this->txt("update"));
            } else {
                $form->addCommandButton(self::CMD_SAVE_AGENDA_ITEM, $this->txt("save"));
            }
        } else {
            if ($update) {
                $form->addCommandButton(self::CMD_UPDATE_FREE_TEXT_AGENDA_ITEM, $this->txt("update"));
            } else {
                $form->addCommandButton(self::CMD_SAVE_FREE_TEXT_AGENDA_ITEM, $this->txt("save"));
            }
        }
        $form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));

        $ti = new ilTextInputGUI($this->txt("title"), self::F_TITLE);
        $ti->setRequired(true);
        $ti->setValidationRegexp("/.{3,}/");
        $ti->setValidationFailureMessage($this->txt("lt_three_chars"));
        $form->addItem($ti);

        $ti = new ilTextAreaInputGUI($this->txt("descripiton"), self::F_DESCRIPTION);
        $ti->setRows(5);
        $form->addItem($ti);

        $cb = new ilCheckboxInputGUI($this->txt("online"), self::F_IS_ACTIVE);
        $cb->setInfo($this->txt("online_byline"));
        $form->addItem($cb);

        if (!$free_text) {
            $ti = new ilTextAreaInputGUI($this->txt("agenda_item_content"), self::F_AGENDA_ITEM_CONTENT);
            $ti->setRows(5);
            $form->addItem($ti);

            $ta = new ilTextAreaInputGUI($this->txt("goals"), self::F_GOALS);
            $ta->setRows(5);
            $form->addItem($ta);
        }

        if ($this->isCCActive()) {
            $ms = new ilMultiSelectInputGUI($this->txt("topic"), self::F_TOPICS);
            $ms->setWidthUnit("%");
            $ms->setWidth(100);
            $ms->setHeightUnit("px");
            $ms->setHeight(93);
            $ms->setOptions($cc_actions->getTopicOptions());
            $form->addItem($ms);
        }

        if ($this->object_actions->isEduTrackingActive()) {
            $sc = new ilSelectInputGUI($this->txt("gdv_learning_content"), self::F_GDV_LEARNING_CONTENT);
            $sc->setDisabled($this->used);
            $sc->setOptions(self::$gdv_content);
            $form->addItem($sc);

            $sc = new ilSelectInputGUI($this->txt("idd_learning_content"), self::F_IDD_LEARNING_CONTENT);
            $sc->setDisabled($this->used);
            $sc->setOptions(self::$idd_content);
            $form->addItem($sc);

            $cb = new ilCheckboxInputGUI($this->txt("idd_relevant"), self::F_IDD_RELEVANT);
            $cb->setDisabled($this->used);
            $cb->setInfo($this->txt("idd_relevant_byline"));
            $form->addItem($cb);
        }

        return $form;
    }

    /**
     * Fill the form with current values.
     *
     * @param 	ilPropertyFormGUI 	$form
     * @param 	AgendaItem\AgendaItem 			$ai
     * @return 	void
     */
    protected function fillForm(ilPropertyFormGUI $form, AgendaItem\AgendaItem $ai, bool $free_text)
    {
        $arr = array(
            self::F_OBJ_ID => $ai->getObjId(),
            self::F_TITLE => $ai->getTitle(),
            self::F_DESCRIPTION => $ai->getDescription(),
            self::F_IS_ACTIVE => $ai->getIsActive(),
            self::F_TOPICS => $ai->getTrainingTopics(),
            self::F_IS_BLANK => $ai->getIsBlank(),
        );

        if ($this->object_actions->isEduTrackingActive()) {
            $arr = array_merge(
                $arr,
                array(
                    self::F_GDV_LEARNING_CONTENT => $ai->getGDVLearningContent(),
                    self::F_IDD_RELEVANT => $ai->getIddRelevant(),
                    self::F_IDD_LEARNING_CONTENT => $ai->getIDDLearningContent()
                )
            );
        }

        if (!$free_text) {
            $arr = array_merge($arr, array(
                self::F_IS_DELETED => $ai->getIsDeleted(),
                self::F_LAST_CHANGE => $ai->getLastChange(),
                self::F_CHANGE_USER_ID => $ai->getChangeUsrId(),
                self::F_POOL_ID => $ai->getPoolId(),
                self::F_GOALS => $ai->getGoals(),
                self::F_AGENDA_ITEM_CONTENT => $ai->getAgendaItemContent()
            ));
        }
        $form->setValuesByArray($arr);
        return $form;
    }

    /**
     * Turns post strings to ints in an array.
     *
     * @param 	string[]
     * @return 	int[]
     */
    protected function toIntArray(array $str) : array
    {
        if (empty($str)) {
            return array();
        }
        return array_map(function ($item) {
            return (int) $item;
        }, $str);
    }

    /**
     * Check whether the plugin CourseClassification is active.
     */
    protected function isCCActive() : bool
    {
        $obj = $this->object_actions->getObject();
        return $obj->isCourseClassificationActive();
    }

    /**
     * Get actions from the plugin CourseClassification.
     *
     * @return ilActions
     */
    protected function getCCActions()
    {
        $obj = $this->object_actions->getObject();
        return $obj->getCourseClassificationActions();
    }
}
