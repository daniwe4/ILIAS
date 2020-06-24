<?php
namespace CaT\Plugins\Accomodation\Reservation;

use \CaT\Plugins\Accomodation;
use \CaT\Plugins\Accomodation\Reservation\Constants;

require_once("Services/Table/classes/class.ilTable2GUI.php");


/**
 * Table for reservations of a single user
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ilUserReservationsSingleTableGUI extends \ilTable2GUI
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
    protected $user_info;


    public function __construct(
        \ilUserReservationsGUI $parent_gui,
        \Closure $txt,
        Accomodation\ilActions $actions
        ) {
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->txt = $txt;

        parent::__construct($parent_gui);

        $this->counter = 0;

        $this->setEnableTitle(true);
        $this->setTitle($this->txt("user_reservations"));
        $this->setTopCommands(false);
        $this->setEnableHeader(true);
        $this->setRowTemplate("tpl.single_user_reservations.html", "Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/");

        $this->addColumn($this->txt("date"), false);
        $this->addColumn($this->txt("table_chk_reservation"), false);
        $this->addColumn($this->txt("table_chk_selfpay"), false);
    }

    public function fillRow($a_set)
    {
        $object = $a_set["object"];
        $errors = $a_set["errors"];
        $message = $a_set["message"];

        $this->tpl->setVariable("DATE", $object['label']);

        $this->tpl->setVariable("POST_VAR", Constants::F_USER_RESERVATION);
        $this->tpl->setVariable("POST_VAR_SELFPAY", Constants::F_USER_SELFPAY);

        if ($object['booked']) {
            $this->tpl->setVariable("BOOK", 'checked');
        }
        if ($object['book_disabled']) {
            $this->tpl->setVariable("BOOK_DISABLED", 'disabled');
        } else {
            $this->tpl->setVariable("BOOK_VALUE", $object['book_value']);
        }

        if ($object['selfpay']) {
            $this->tpl->setVariable("SELFPAY", 'checked');
        }
        if ($object['selfpay_disabled']) {
            $this->tpl->setVariable("SELFPAY_DISABLED", 'disabled');
        } else {
            $this->tpl->setVariable("SELFPAY_VALUE", $object['book_value']);
        }


        $this->counter++;
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
