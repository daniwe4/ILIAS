<?php

namespace CaT\Plugins\CopySettings\Children;

use CaT\Plugins\CopySettings;

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';

class ilChildrenSettingsTableGUI extends \ilTable2GUI
{
    use CopySettings\Helper;

    /**
     *
     * @param object $a_parent_class
     * @param string $a_parent_cmd
     * @return
     */
    public function __construct($a_parent_class, $a_parent_cmd, CopySettings\ilObjectActions $actions, \Closure $txt)
    {
        parent::__construct($a_parent_class, $a_parent_cmd);

        global $DIC, $objDefinition;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_access = $DIC->access();
        $this->g_tree = $DIC->repositoryTree();
        $this->g_object_definition = $objDefinition;

        $this->txt = $txt;

        $this->addColumn($this->txt('title'), '', '55%');
        $this->addColumn($this->txt('copy'), '', '15%');
        $this->addColumn($this->txt('link'), '', '15%');
        $this->addColumn($this->txt('omit'), '', '15%');

        $this->setEnableHeader(true);
        $this->setFormAction($this->g_ctrl->getFormAction($this->getParentObject()));
        $this->setRowTemplate("tpl.obj_copy_selection_row.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings");
        $this->setEnableTitle(true);
        $this->setEnableNumInfo(true);
        $this->setLimit(999999);

        $this->setFormName('cmd');

        $this->actions = $actions;
        $this->copy_settings_ref_id = $this->actions->getRefId();
    }

    /**
     * Get childs of container as data source for table
     *
     * @param int 	$container_ref_id
     *
     * @return void
     */
    public function parseSource($container_ref_id)
    {
        $first = true;
        foreach ($this->g_tree->getSubTree($root = $this->g_tree->getNodeData($container_ref_id)) as $node) {
            if ($node['type'] == 'rolf') {
                continue;
            }
            if (!$this->g_access->checkAccess('visible', '', $node['child'])) {
                continue;
            }

            if ($this->copy_settings_ref_id == $node['child']) {
                continue;
            }

            $r = array();
            $r['last'] = false;
            $r['source'] = $first;
            $r['ref_id'] = $node['child'];
            $r['depth'] = $node['depth'] - $root['depth'];
            $r['type'] = $node['type'];
            $r['title'] = $node['title'];
            $r['copy'] = $this->g_object_definition->allowCopy($node['type']);
            $r['perm_copy'] = $this->g_access->checkAccess('copy', '', $node['child']);
            $r['link'] = $this->g_object_definition->allowLink($node['type']);
            $r['perm_link'] = true;

            if (!trim($r['title']) && $r['type'] == 'sess') {
                include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
                $app_info = \ilSessionAppointment::_lookupAppointment($node["obj_id"]);
                $r['title'] = \ilSessionAppointment::_appointmentToString($app_info['start'], $app_info['end'], $app_info['fullday']);
            }

            $rows[] = $r;
            $first = false;
        }

        $rows[] = array('last' => true);
        $this->setData((array) $rows);
    }

    /**
     * @inheritdoc
     */
    protected function fillRow($s)
    {
        if ($s['last']) {
            $this->tpl->setCurrentBlock('footer_copy');
            $this->tpl->setVariable('TXT_COPY_ALL', $this->txt('copy_all'));
            $this->tpl->setVariable('VALUE_COPY_ALL', Child::COPY);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_link');
            $this->tpl->setVariable('TXT_LINK_ALL', $this->txt('link_all'));
            $this->tpl->setVariable('VALUE_LINK_ALL', Child::REFERENCE);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock('footer_omit');
            $this->tpl->setVariable('TXT_OMIT_ALL', $this->txt('omit_all'));
            $this->tpl->setVariable('VALUE_OMIT_ALL', Child::NOTHING);
            $this->tpl->parseCurrentBlock();
            return true;
        }

        $copy_settings = $this->actions->getCopySettingsByRefId((int) $s['ref_id']);

        for ($i = 0; $i < $s['depth']; $i++) {
            $this->tpl->touchBlock('padding');
            $this->tpl->touchBlock('end_padding');
        }
        $this->tpl->setVariable('TREE_IMG', \ilObject::_getIcon(\ilObject::_lookupObjId($s['ref_id']), "tiny", $s['type']));
        $this->tpl->setVariable('TREE_ALT_IMG', $this->txt('obj_' . $s['type']));
        $this->tpl->setVariable('TREE_TITLE', $s['title']);

        if ($s['source']) {
            return true;
        }

        // Copy
        if ($s['perm_copy'] and $s['copy']) {
            $this->tpl->setCurrentBlock('radio_copy');
            $this->tpl->setVariable('TXT_COPY', $this->txt('copy'));
            $this->tpl->setVariable('NAME_COPY', 'cp_options[' . $s['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_COPY', Child::COPY);
            $this->tpl->setVariable('ID_COPY', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_copy');

            if ($copy_settings && $copy_settings->getProcessType() == Child::COPY) {
                $this->tpl->setVariable('COPY_CHECKED', 'checked="checked"');
            }

            $this->tpl->parseCurrentBlock();
        } elseif ($s['copy']) {
            $this->tpl->setCurrentBlock('missing_copy_perm');
            $this->tpl->setVariable('TXT_MISSING_COPY_PERM', $this->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Link
        if ($s['perm_link'] and $s['link']) {
            $this->tpl->setCurrentBlock('radio_link');
            $this->tpl->setVariable('TXT_LINK', $this->txt('link'));
            $this->tpl->setVariable('NAME_LINK', 'cp_options[' . $s['ref_id'] . '][type]');
            $this->tpl->setVariable('VALUE_LINK', Child::REFERENCE);
            $this->tpl->setVariable('ID_LINK', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_link');
            if ($copy_settings && $copy_settings->getProcessType() == Child::REFERENCE) {
                $this->tpl->setVariable('LINK_CHECKED', 'checked="checked"');
            }
            $this->tpl->parseCurrentBlock();
        } elseif ($s['link']) {
            $this->tpl->setCurrentBlock('missing_link_perm');
            $this->tpl->setVariable('TXT_MISSING_LINK_PERM', $this->txt('missing_perm'));
            $this->tpl->parseCurrentBlock();
        }

        // Omit
        $this->tpl->setCurrentBlock('omit_radio');
        $this->tpl->setVariable('TXT_OMIT', $this->txt('omit'));
        $this->tpl->setVariable('NAME_OMIT', 'cp_options[' . $s['ref_id'] . '][type]');
        $this->tpl->setVariable('VALUE_OMIT', Child::NOTHING);
        $this->tpl->setVariable('ID_OMIT', $s['depth'] . '_' . $s['type'] . '_' . $s['ref_id'] . '_omit');

        if (($copy_settings && $copy_settings->getProcessType() == Child::NOTHING) || $copy_settings === null) {
            $this->tpl->setVariable('OMIT_CHECKED', 'checked="checked"');
        }
        $this->tpl->parseCurrentBlock();
    }
}
