<?php
namespace CaT\Plugins\CourseCreation\Requests;

require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for open requests
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilRequestsTableGUI extends \ilTable2GUI
{/**
     * @var \ilOpenRequestsGUI
     */
    protected $parent_gui;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var \Closure
     */
    protected $fill_row;

    public function __construct(RequestsGUI $parent_gui, $parent_cmd, \Closure $fill_row)
    {
        $this->fill_row = $fill_row;
        $this->setId(get_class($parent_gui));
        parent::__construct($parent_gui, $parent_cmd);
        $this->setEnableTitle(true);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setExternalSorting(true);
        $this->setExternalSegmentation(true);
        $this->setShowRowsSelector(false);
        $this->setDefaultOrderDirection("asc");
    }

    public function fillRow($a_set)
    {
        $fnc = $this->fill_row;
        $fnc($this, $a_set);
    }
}
