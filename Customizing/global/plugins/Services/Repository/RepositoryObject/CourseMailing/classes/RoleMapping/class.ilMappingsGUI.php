<?php

declare(strict_types=1);

use \CaT\Plugins\CourseMailing;
use \CaT\Plugins\CourseMailing\RoleMapping\RoleMapping;
use \CaT\Plugins\CourseMailing\RoleMapping\RoleMappingBackend;
use \CaT\Plugins\CourseMailing\RoleMapping\ilMappingsTableGUI;

/**
 * GUI for Role Mappings
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilMappingsGUI extends TMSTableParentGUI
{
    use CourseMailing\ilTxtClosure;

    const TABLE_ID = 'table_rolemappings';

    const CMD_EDIT = "editMappings";
    const CMD_SAVE = "saveMappings";

    const F_TEMPLATE = 'f_template';
    const F_MAPPING_ID = 'f_id';
    const F_ROLE_ID = "f_role_id";
    const F_ROLE_TITLE = "f_role_title";
    const F_ATTACHMENTS = 'f_attachments';

    const MAIL_TEMPLATE_CONTEXT = 'crs_context_invitation';

    const NO_MAIL_OPTION = 0;
    const NEW_MAPPING = -1;

    /**
     * @var mixed
     */
    protected $parent_gui;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var CourseMailing\ilActions
     */
    protected $actions;

    /**
     * @var array
     */
    protected $template_options;

    /**
     * @var array
     */
    protected $attachment_options;

    public function __construct($parent_gui, $actions, \Closure $txt)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_access = $DIC->access();
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws \Exception
     * @return void
     */
    public function executeCommand()
    {
        $parent_gui_class = get_class($this->parent_gui);
        $cmd = $this->g_ctrl->getCmd();
        switch ($cmd) {
            case self::CMD_SAVE:
                $this->saveMappings();
                break;
            case $parent_gui_class::DEFAULT_CMD_CONTENT:
            case self::CMD_EDIT:
            default:
                $this->editMappings();
                break;
        }
    }

    /**
     * command: show the editing GUI
     *
     * @return void
     */
    protected function editMappings()
    {
        $table = $this->getTable();
        $mappings = $this->actions->getPossibleRoleMappings();
        $data = $this->createProcessingArray($mappings);
        $table->setData($data);

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * command: save mappings
     *
     * @return void
     */
    protected function saveMappings()
    {
        $post = $_POST;
        $role_ids = $post[self::F_ROLE_ID];
        $role_ids = array_map(
            function ($role_id) {
                return (int) $role_id;
            },
            $role_ids
        );
        foreach ($role_ids as $counter => $role_id) {
            $template_id = (int) $post[self::F_TEMPLATE][$counter];
            $mapping_id = (int) $post[self::F_MAPPING_ID][$counter];
            $attachments = $post[self::F_ATTACHMENTS][$role_id];

            if (is_null($attachments)) {
                $attachments = [];
            }

            if ($mapping_id == self::NEW_MAPPING && $template_id == self::NO_MAIL_OPTION) {
                continue;
            }

            if ($mapping_id != self::NEW_MAPPING && $template_id == self::NO_MAIL_OPTION) {
                $this->actions->deleteForMappingId($mapping_id);
                continue;
            }

            if ($mapping_id == self::NEW_MAPPING && $template_id != self::NO_MAIL_OPTION) {
                $this->createNewMapping(
                    $mapping_id,
                    $role_id,
                    $template_id,
                    $attachments
                );
                continue;
            }

            $this->updateExistingMapping($mapping_id, $template_id, $attachments);
        }
        \ilUtil::sendInfo($this->txt("msg_saved"), true);
        $this->g_ctrl->redirect($this, self::CMD_EDIT);
    }

    protected function createNewMapping(
        int $mapping_id,
        int $role_id,
        int $template_id,
        array $attachments
    ) {
        $this->actions->createNewMapping(
            $mapping_id,
            $role_id,
            $template_id,
            $attachments
        );
    }

    protected function updateExistingMapping(
        int $mapping_id,
        int $template_id,
        array $attachments
    ) {
        $mapping = $this->actions->getSingleRoleMapping($mapping_id);
        $mapping = $mapping
            ->withTemplateId($template_id)
            ->withAttachmentIds($attachments);
        $this->actions->updateSingleRoleMapping($mapping);
    }

    /**
     * Create an array of entries for processing the role mapping table.
     *
     * @param RoleMapping[] 	$objects
     * @return array<string,RoleMapping>
     */
    protected function createProcessingArray(array $objects)
    {
        $ret = array();
        foreach ($objects as $object) {
            $ret[] = array("object" => $object);
        }
        return $ret;
    }

    protected function getTable() : ilTMSTableGUI
    {
        $table = $this->getTMSTableGUI();
        $table->setTitle($this->txt("table_rolemappings_title"));
        $table->setTopCommands(false);
        $table->setRowTemplate(
            "tpl.role_mappings_row.html",
            $this->actions->getPluginDirectory()
        );
        $table->setFormAction($this->g_ctrl->getFormAction($this));

        $table->addColumn($this->txt("table_rolemappings_localrole"), false);
        $table->addColumn($this->txt("table_rolemappings_mailtemplate"), false);
        $table->addColumn($this->txt("table_rolemappings_attachments"), false);

        $table->addCommandButton(self::CMD_SAVE, $this->txt("save"));
        $table->addCommandButton(self::CMD_EDIT, $this->txt("cancel"));

        return $table;
    }

    /**
     * Get the closure table should be filled with
     *
     * @return \Closure
     */
    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, array $a_set) {
            /** @var RoleMapping $role_mapping */
            $role_mapping = $a_set["object"];
            $tpl = $table->getTemplate();

            $tpl->setVariable("POST_PREFIX", self::F_MAPPING_ID);
            $tpl->setVariable("MAPPING_ID", $role_mapping->getId());

            $tpl->setVariable("POST_PREFIX_ROLE", self::F_ROLE_ID);
            $tpl->setVariable("ROLE_ID", $role_mapping->getRoleId());

            $role_title = $role_mapping->getRoleTitle();
            if (substr($role_title, 0, 3) === 'il_') {
                $role_title = $this->txt($role_title);
            }
            $tpl->setVariable("LOCAL_ROLE_TITLE", $role_title);

            $input = $this->buildSelectionInput($role_mapping->getTemplateId());
            $tpl->setVariable("MAIL_TEMPLATE_INPUT", $input->render());

            $attachment_input = $this->buildMultiSelectionInput($role_mapping->getRoleId(), $role_mapping->getAttachmentIds());
            $tpl->setVariable("ATTACHMENTS_INPUT", $attachment_input->render());
        };
    }

    /**
     * @return \ilSelectInputGUI
     */
    private function buildSelectionInput($value)
    {
        $si = new \ilSelectInputGUI('', self::F_TEMPLATE . '[]');
        $si->setOptions($this->getTemplateOptions());
        $si->setValue($value);
        return $si;
    }

    /**
     * @return \ilMultiSelectInputGUI
     */
    private function buildMultiSelectionInput(int $role_id, array $values)
    {
        $si = new \ilMultiSelectInputGUI('', self::F_ATTACHMENTS . '[' . $role_id . ']');
        $si->setOptions($this->getAttachmentOptions());
        $si->setValue($values);
        $si->setWidth(200);
        return $si;
    }

    protected function getTemplateOptions() : array
    {
        if (is_null($this->template_options)) {
            $this->template_options = $this->buildTemplateOptions();
        }
        return $this->template_options;
    }

    protected function getAttachmentOptions() : array
    {
        if (is_null($this->attachment_options)) {
            $this->attachment_options = $this->actions->getAttachmentOptions();
        }
        return $this->attachment_options;
    }

    /**
     * @return array<string,string>
     */
    private function buildTemplateOptions()
    {
        $options = array();
        $options[self::NO_MAIL_OPTION] = $this->txt('table_rolemapping_nomail_option');
        $templates = $this->actions->getAvailableMailTemplates(array(self::MAIL_TEMPLATE_CONTEXT));
        foreach ($templates as $template) {
            $options[$template->getTplId()] = $template->getTitle();
        }
        return $options;
    }

    /**
     * Get the basic command table has to use
     *
     * @return string
     */
    protected function tableCommand()
    {
        return self::CMD_EDIT;
    }

    /**
     * Get the id of table
     *
     * @return string
     */
    protected function tableId()
    {
        return  self::TABLE_ID;
    }
}
