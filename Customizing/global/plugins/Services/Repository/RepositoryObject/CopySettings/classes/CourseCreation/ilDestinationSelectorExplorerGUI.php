<?php
/* Copyright (c) 2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\CopySettings\CourseCreation;

require_once("Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Explorer for selecting repository items.
 *
 * This is a special implementation for the course creation
 *
 * @author	Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @version	$Id$
 */
class ilDestinationSelectorExplorerGUI extends \ilRepositorySelectorExplorerGUI
{
    /**
     * Constructor
     *
     * @param object $a_parent_obj parent gui object
     * @param string $a_parent_cmd parent cmd that renders the explorer
     * @param object/string $a_selection_gui gui class that should be called for the selection command
     * @param string $a_selection_cmd selection command
     * @param string $a_selection_par selection parameter
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "selectObject",
        $a_selection_par = "sel_ref_id",
        $a_id = "rep_exp_sel"
    ) {
        parent::__construct(
            $a_parent_obj,
            $a_parent_cmd,
            $a_selection_gui,
            $a_selection_cmd,
            $a_selection_par,
            $a_id
        );

        global $DIC;
        $this->g_access = $DIC->access();
    }
    /**
     * Is node selectable?
     *
     * @param mixed $a_node node object/array
     * @return boolean node selectable true/false
     */
    protected function isNodeSelectable($a_node)
    {
        return $this->g_access->checkAccess("create_crs", "", $a_node["child"]);
    }

    /**
     * Is node visible
     *
     * @param array $a_node node data
     * @return bool visible true/false
     */
    public function isNodeVisible($a_node)
    {
        if ($a_node["ref_id"] == $this->getNodeId($this->getRootNode())) {
            return false;
        }

        if (!$this->g_access->checkAccess('visible', '', $a_node["child"])
            && !$this->g_access->checkAccess('read', '', $a_node["child"])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Get childs of node
     *
     * @param int $a_parent_node_id node id
     * @return array childs array
     */
    public function getChildsOfNode($a_parent_node_id)
    {
        $children = parent::getChildsOfNode($a_parent_node_id);

        $children = array_filter($children, function ($c) {
            return $this->g_access->checkAccess('visible', '', $c["child"])
                    && $this->g_access->checkAccess('read', '', $c["child"]);
        });

        return $children;
    }

    /**
     * Get on load code
     *
     * @param
     * @return
     */
    public function getOnLoadCode()
    {
        global $ilCtrl;

        $container_id = $this->getContainerId();
        $container_outer_id = "il_expl2_jstree_cont_out_" . $this->getId();

        // collect open nodes
        $open_nodes = array($this->getDomNodeIdForNodeId($this->getNodeId($this->getRootNode())));
        foreach ($this->open_nodes as $nid) {
            $open_nodes[] = $this->getDomNodeIdForNodeId($nid);
        }
        foreach ($this->custom_open_nodes as $nid) {
            $dnode = $this->getDomNodeIdForNodeId($nid);
            if (!in_array($dnode, $open_nodes)) {
                $open_nodes[] = $dnode;
            }
        }

        // ilias config options
        $url = "";
        if (!$this->getOfflineMode()) {
            $url = $ilCtrl->getLinkTargetByClass("ilCourseCreationGUI", $this->parent_cmd, "", true);
        }

        // secondary highlighted nodes
        $shn = array();
        foreach ($this->sec_highl_nodes as $sh) {
            $shn[] = $this->getDomNodeIdForNodeId($sh);
        }
        $config = array(
            "container_id" => $container_id,
            "container_outer_id" => $container_outer_id,
            "url" => $url,
            "second_hnodes" => $shn,
            "ajax" => $this->getAjax(),
        );


        // jstree config options
        $js_tree_config = array(
            "core" => array(
                "animation" => 300,
                "initially_open" => $open_nodes,
                "open_parents" => false,
                "strings" => array("loading" => "Loading ...", "new_node" => "New node")
            ),
            "plugins" => $this->getJSTreePlugins(),
            "themes" => array("dots" => false, "icons" => false, "theme" => ""),
            "html_data" => array()
        );

        return 'il.Explorer2.init(' . json_encode($config) . ', ' . json_encode($js_tree_config) . ');';
    }
}
