<?php
namespace CaT\Plugins\Accomodation\Reservation;

use \CaT\Plugins\Accomodation;
use \CaT\Plugins\Accomodation\Reservation\Constants;

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once(__DIR__ . "/class.ilUserReservationsGUI.php");

/**
 * Table for all user reservations
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilUserReservationsTableGUI extends \ilTable2GUI
{
    /**
     * @var \ilUserReservationsGUI
     */
    protected $parent_gui;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var Accomodation\ilActions
     */
    protected $actions;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @var \Closure
     */
    protected $date_presentation;

    /**
     * @var string
     */
    protected $edit_user_link;

    public function __construct(
        \ilUserReservationsGUI $parent_gui,
        \Closure $txt,
        Accomodation\ilActions $actions,
        $edit_user_link,
        $parent_default_cmd
        ) {
        assert('is_string($edit_user_link)');
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->txt = $txt;
        $this->edit_user_link = $edit_user_link;

        $this->setId('table_user_reservations');
        parent::__construct($parent_gui, $parent_default_cmd);

        $this->counter = 0;

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("user_reservations"));
        $this->setTopCommands(true);
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.reservations.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/");
        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->setShowRowsSelector(true);
        $this->setFormAction($this->g_ctrl->getFormAction($parent_gui));
        $this->setDefaultOrderField(\ilUserReservationsGUI::COLUMN_LASTNAME);
        $this->setDefaultOrderDirection("asc");
        $this->setSelectAllCheckbox(Constants::F_RES_TABLE_POSTVAR_ROW_IDS);

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->txt("table_user_lastname"), \ilUserReservationsGUI::COLUMN_LASTNAME);
        $this->addColumn($this->txt("table_user_firstname"), \ilUserReservationsGUI::COLUMN_FIRSTNAME);
        $this->addColumn($this->txt("table_user_login"), \ilUserReservationsGUI::COLUMN_LOGIN);
        $this->addColumn($this->txt("table_user_role"), \ilUserReservationsGUI::COLUMN_ROLE);
        $this->addColumn($this->txt("table_user_mail"), \ilUserReservationsGUI::COLUMN_MAIL);
        $this->addColumn($this->txt("table_user_phone"));
        $this->addColumn($this->txt("table_user_reservations"));
        $this->addColumn($this->txt("table_note"));
        $this->addColumn("");
    }

    public function fillRow($a_set)
    {
        $this->tpl->setVariable("ROW_SELECTION_POSTVAR", Constants::F_RES_TABLE_POSTVAR_ROW_IDS);
        $this->tpl->setVariable("USER_ID", $a_set["USER_ID"]);
        $this->tpl->setVariable("COUNTER", $this->counter);
        $this->tpl->setVariable("USER_LASTNAME", $a_set[\ilUserReservationsGUI::COLUMN_LASTNAME]);
        $this->tpl->setVariable("USER_FIRSTNAME", $a_set[\ilUserReservationsGUI::COLUMN_FIRSTNAME]);
        $this->tpl->setVariable("USER_LOGIN", $a_set[\ilUserReservationsGUI::COLUMN_LOGIN]);
        $this->tpl->setVariable("USER_ROLE", $a_set[\ilUserReservationsGUI::COLUMN_ROLE]);
        $this->tpl->setVariable("USER_EMAIL", $a_set[\ilUserReservationsGUI::COLUMN_MAIL]);
        $this->tpl->setVariable("USER_PHONE", $a_set[\ilUserReservationsGUI::COLUMN_PHONE]);
        $this->tpl->setVariable("RESERVATIONS", implode('<br>', $a_set[\ilUserReservationsGUI::COLUMN_RESERVATIONS]));
        $this->tpl->setVariable("NOTE", $a_set[\ilUserReservationsGUI::COLUMN_NOTE]);
        $this->tpl->setVariable("CMD_EDIT_USER", $this->edit_user_link);
        $this->tpl->setVariable("CMD_EDIT_USER_PARAM", Constants::F_RES_TABLE_GETVAR_USR_ID);
        $this->tpl->setVariable("EDIT_USER_BUTTON", $this->txt('table_user_edit_button'));

        $this->counter++;
    }

    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }


    /**
     * @param 	int	$usr_id
     * @return	string
     */
    private function getUserRoles($usr_id)
    {
        $roles = $this->actions->getCourseRolesOfUser($usr_id);
        return implode('<br>', $roles);
    }
}
