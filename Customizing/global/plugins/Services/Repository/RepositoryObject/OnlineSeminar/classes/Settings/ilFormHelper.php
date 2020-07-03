<?php

namespace CaT\Plugins\OnlineSeminar\Settings;

/**
 * This trait defines functions you need to create form elements or values
 * for the settings. E.g. the Object has additional setting "OrgUnit".
 */
trait ilFormHelper
{
    protected static $admission_options = array("self_booking",
        "no_self_booking",
        "book_from_course"
    );

    protected function addPropertyItems(\ilPropertyFormGUI $form)
    {
        $ne = new \ilNonEditableValueGUI($this->txt("vc"), self::F_VC);
        $form->addItem($ne);

        require_once("Services/Form/classes/class.ilDateDurationInputGUI.php");
        $du = new \ilDateDurationInputGUI($this->txt("schedule"), self::F_SCHEDULE);
        $du->setShowTime(true);
        $form->addItem($du);

        $opt = new \ilRadioGroupInputGUI($this->txt("admission"), self::F_ADMISSION);
        foreach (static::$admission_options as $value) {
            $option = new \ilRadioOption($this->txt($value), $value);
            $opt->addOption($option);
        }
        $form->addItem($opt);

        $ti = new \ilTextInputGUI($this->txt("url"), self::F_URL);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $cb = new \ilCheckboxInputGUI($this->txt("online"), self::F_ONLINE);
        $form->addItem($cb);
    }
}
