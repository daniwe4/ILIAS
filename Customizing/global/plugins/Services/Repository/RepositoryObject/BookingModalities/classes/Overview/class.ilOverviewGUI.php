<?php
use CaT\Plugins\BookingModalities\Overview\ilOverviewTableGUI;
use CaT\Plugins\BookingModalities\ilObjectActions;
use CaT\Plugins\BookingModalities\Overview\Overview;

require_once __DIR__ . "/../class.ilObjBookingModalitiesGUI.php";
require_once "Services/TMS/Table/TMSTableParentGUI.php";

/**
 * GUI for an overview of actual bookings on the parent course object.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training>
 */
class ilOverviewGUI extends TMSTableParentGUI
{
    const CMD_SHOW_BOOKINGS = "showBookings";
    const CMD_SHOW_CANCELLATIONS = "showCancellations";

    const S_LASTNAME = "lastname";
    const S_FIRSTNAME = "firstname";
    const S_LOGIN = "login";
    const S_STATUS = "status";
    const S_DATE = "date";
    const S_BOOKING_DATE = "booking_date";
    const S_CANCEL_DATE = "cancel_booking_date";
    const S_BOOKER = "booker";

    const DEFAULT_ORDER_DIRECTION = "asc";

    const BS_PARTICIPANT = "participant";
    const BS_CANCELLED = "cancelled";
    const BS_WAITING_CANCELLED = "waiting_cancelled";
    const BS_WAITING_SELF_CANCELLED = "waiting_self_cancelled";
    const BS_CANCELLED_AFTER_DEADLINE = "cancelled_after_deadline";
    const BS_WAITING = "waiting";

    const TABLE_BOKKINGS_ID = "table_bookings";
    const TABLE_CANCELLATIONS_ID = "table_cancellations";

    /**
     * @var ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var ilObjBookingModalitiesGUI
     */
    protected $parent;

    /**
     * @var ilObjectActions
     */
    protected $actions;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * Constructor of the class ilBookingsGUI.
     */
    public function __construct(
        ilObjBookingModalitiesGUI $parent,
        ilObjectActions $actions
    ) {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();

        $this->parent = $parent;
        $this->actions = $actions;
        $this->txt = $actions->getObject()->txtClosure();
    }

    /**
     * Required function of ILIAS forwardCommand system.
     * Delegate function to use according to forwarded command.
     *
     * @throws Exception Coammnd is unknwon.
     */
    public function executeCommand()
    {
        $cmd = $this->g_ctrl->getCmd();
        $this->command = $cmd;
        switch ($cmd) {
            case self::CMD_SHOW_BOOKINGS:
            case self::CMD_SHOW_CANCELLATIONS:
                $this->$cmd();
                break;
            default:
                throw new Exception(__METHOD__ . " unkown command " . $cmd);
        }
    }

    /**
     * Shows bookings as a table.
     *
     * @return void
     */
    protected function showBookings()
    {
        if (is_null($this->actions->getObject()->getParentCourse())) {
            ilUtil::sendInfo($this->txt("not_below_crs"));
            return;
        }

        $table = $this->getTable($this->txt("booking_table_title"), self::S_BOOKING_DATE);

        $limit = (int) $table->getLimit();
        $offset = (int) $table->getOffset();
        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();

        $selected_columns = $table->getSelectedColumns();
        $bookings = $this->actions->getBookings($order_field, $order_direction, $limit, $offset, $selected_columns);

        $table->setData($bookings);
        $table->setMaxCount($this->actions->getMaxBookings());

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * Shows cancellations as a table.
     *
     * @return void
     */
    protected function showCancellations()
    {
        if (is_null($this->actions->getObject()->getParentCourse())) {
            ilUtil::sendInfo($this->txt("not_below_crs"));
            return;
        }

        $table = $this->getTable($this->txt("cancellation_table_title"), self::S_CANCEL_DATE);

        $limit = (int) $table->getLimit();
        $offset = (int) $table->getOffset();
        $order_field = $table->getOrderField();
        $order_direction = $table->getOrderDirection();

        $selected_columns = $table->getSelectedColumns();
        $cancellation = $this->actions->getCancellations($order_field, $order_direction, $limit, $offset, $selected_columns);

        $table->setData($cancellation);
        $table->setMaxCount($this->actions->getMaxCancellations());

        $this->g_tpl->setContent($table->getHtml());
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return $this->command;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        switch ($this->tableCommand()) {
            case self::CMD_SHOW_BOOKINGS:
                return self::TABLE_BOKKINGS_ID;
                break;
            case self::CMD_SHOW_CANCELLATIONS:
                return self::TABLE_CANCELLATIONS_ID;
                break;
            default:
                throw new Exception("unknown table command " . $this->tableCommand());
        }
    }

    /**
     * Get all additional columns that could be selected.
     *
     * @return array
     */
    public function getSelectableColumns()
    {
        include_once './Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php';
        $ef = ilExportFieldsInfo::_getInstanceByType($this->actions->getObject()->getParentCourse()->getType());
        $all_columns = $ef->getSelectableFieldsInfo($this->actions->getObject()->getParentCourse()->getId());

        unset($all_columns['login']);
        return $all_columns;
    }

    /**
     * Initialize a table.
     *
     * @param  string $title
     * @return ilTMSTableGUI
     */
    protected function getTable($title, $date_field)
    {
        require_once("Services/TMS/Table/ilTMSTableGUI.php");

        $table = $this->getTMSTableGUI();

        $table->setRowTemplate("tpl.overview_table.html", $this->actions->getObject()->getDirectory());
        $table->setTitle($title);
        $table->setExternalSegmentation(true);
        $table->setDefaultOrderField(self::S_LASTNAME);
        $table->setDefaultOrderDirection(self::DEFAULT_ORDER_DIRECTION);
        $table->setFormAction($this->g_ctrl->getFormAction($this));
        $table->determineOffsetAndOrder();
        $table->determineLimit();

        $table->addColumn($this->txt(self::S_LASTNAME), self::S_LASTNAME);
        $table->addColumn($this->txt(self::S_FIRSTNAME), self::S_FIRSTNAME);
        $table->addColumn($this->txt(self::S_LOGIN), self::S_LOGIN);
        $table->addColumn($this->txt(self::S_STATUS), self::S_STATUS);
        $table->addColumn($this->txt(self::S_DATE), $date_field);
        $table->addColumn($this->txt(self::S_BOOKER), self::S_BOOKER);

        $columns = $table->getSelectedColumns();
        if (count($columns) > 0) {
            $selectables = $table->getSelectableColumns();

            foreach ($columns as $column) {
                if (array_key_exists($column, $selectables)) {
                    $table->addColumn($selectables[$column]['txt']);
                }
            }
        }

        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        return function ($table, $set) {
            $date = $this->getDateForStatus($set);
            $date = $date->get(IL_CAL_FKT_DATE, "d.m.Y");
            if (is_null($date)) {
                $date = "-";
            }
            $tpl = $table->getTemplate();

            $tpl->setVariable("LASTNAME", $set->getLastname());
            $tpl->setVariable("FIRSTNAME", $set->getFirstname());
            $tpl->setVariable("LOGIN", $set->getLogin());
            $tpl->setVariable("STATUS", $this->txt($set->getStatus()));
            $tpl->setVariable("DATE", $date);
            $tpl->setVariable("BOOKER", $set->getBooker());

            foreach ($set->getAdditionalFields() as $val) {
                $tpl->setCurrentBlock('custom_fields');
                $tpl->setVariable('VAL_CUST', $val);
                $tpl->parseCurrentBlock();
            }
        };
    }

    /**
     * Get date for specific status.
     *
     * @param  Overview $set
     * @return ilDateTime
     */
    protected function getDateForStatus($set)
    {
        switch ($set->getStatus()) {
            case self::BS_PARTICIPANT:
                return $set->getBookingDate();
                break;
            case self::BS_WAITING:
                return $set->getWaitingDate();
                break;
            case self::BS_CANCELLED:
            case self::BS_CANCELLED_AFTER_DEADLINE:
                return $set->getCancelBookingDate();
                break;
            case self::BS_WAITING_CANCELLED:
            case self::BS_WAITING_SELF_CANCELLED:
                return $set->getCancelWaitingDate();
                break;
            default:
                throw new Exception("Unknown booking status " . $set->getStatus() . ".");
                break;
        }
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
