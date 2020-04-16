<?php
require_once 'Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php';
require_once 'Services/Form/classes/class.ilCheckboxInputGUI.php';

/**
* This class represents a checkbox property in a property form.
* add some css like this to properly align inputs:
*
* label.col-sm-3.control-label.il_no_label_checkbox {
*   width: 0px;
*   padding: 0px;
* }
*
* @author Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class ilNoLabelCheckboxInputGUI extends ilCheckboxInputGUI
{

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("no_label_checkbox");
    }
}
