<?php
require_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Table for upcoming scheduled events
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilScheduleTableGUI extends \ilTable2GUI
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var int
     */
    protected $counter;


    public function __construct(
        \ilScheduledEventsConfigGUI $parent_gui,
        \Closure $txt,
        $plugin_direcotry
        ) {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->txt = $txt;
        $this->setId('table_upcoming_events');
        parent::__construct($parent_gui);

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("table_title"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.role_mappings_row.html", $plugin_direcotry);
        $this->setExternalSorting(true);

        $this->setColumns();
    }


    protected function setColumns()
    {
        $this->addColumn($this->txt("table_col_id"), false);
        $this->addColumn($this->txt("table_col_issuer_ref"), false);
        $this->addColumn($this->txt("table_col_due"), false);
        $this->addColumn($this->txt("table_col_component"), false);
        $this->addColumn($this->txt("table_col_event"), false);
        $this->addColumn($this->txt("table_col_params"), false);
    }

    public function fillRow($a_set)
    {
        $object = $a_set;

        foreach ($object->getParameters() as $k => $v) {
            $params[] = $k . '=>' . $v;
        }
        $params = implode('<br>', $params);

        $this->tpl->setVariable("ID", $object->getId());
        $this->tpl->setVariable("ISSUER_REF", $object->getIssuerRef());
        $this->tpl->setVariable("DUE", $object->getDue()->format(self::DATETIME_FORMAT));
        $this->tpl->setVariable("COMPONENT", $object->getComponent());
        $this->tpl->setVariable("EVENT", $object->getEvent());
        $this->tpl->setVariable("PARAMS", $params);
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt(string $code)
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
