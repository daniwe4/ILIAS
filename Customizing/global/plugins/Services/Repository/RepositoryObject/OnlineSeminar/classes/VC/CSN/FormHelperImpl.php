<?php

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use CaT\Plugins\OnlineSeminar\VC;

/**
 * Deliver and evaluates form settings form values required by vc
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class FormHelperImpl implements VC\FormHelper
{
    const F_PHONE = "f_phone";
    const F_PIN = "f_pin";
    const F_REQUIRED_MINUTES = "f_required_minutes";
    const F_UPLOAD_REQUIRED = "f_upload_required";

    /**
     * @var VCActions
     */
    protected $vc_actions;

    public function __construct(\ilObjOnlineSeminar $object, VC\VCActions $vc_actions)
    {
        $this->vc_actions = $vc_actions;
        $this->object = $object;
    }

    /**
     * @inheritdoc
     */
    public function addRequiredFormItems(\ilPropertyFormGUI $form)
    {
        $title_section = new \ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("csn_settings"));
        $form->addItem($title_section);

        $ti = new \ilTextInputGUI($this->txt("csn_settings_phone"), self::F_PHONE);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("pin"), self::F_PIN);
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("required_minutes"), self::F_REQUIRED_MINUTES);
        $ti->setInfo($this->txt("required_minutes_info"));
        $form->addItem($ti);

        $cb = new \ilCheckboxInputGUI($this->txt("upload_required"), self::F_UPLOAD_REQUIRED);
        $cb->setInfo($this->txt("upload_required_info"));
        $form->addItem($cb);
    }

    /**
     * @inheritdoc
     */
    public function saveRequiredValues(array $values)
    {
        $settings = $this->vc_actions->select();

        $required_minutes = $values[self::F_REQUIRED_MINUTES];
        if ($required_minutes == "") {
            $required_minutes = null;
        } else {
            $required_minutes = (int) $required_minutes;
        }

        $settings = $settings->withPhone($this->stringToNull($values[self::F_PHONE]))
            ->withPin($this->stringToNull($values[self::F_PIN]))
            ->withMinutesRequired($required_minutes)
            ->withIsUploadRequired((bool) $values[self::F_UPLOAD_REQUIRED]);

        $this->vc_actions->update($settings);
    }

    /**
     * @inheritdoc
     */
    public function getFormValues(array &$values)
    {
        $settings = $this->vc_actions->select();

        $values[self::F_PHONE] = $settings->getPhone();
        $values[self::F_PIN] = $settings->getPin();
        $values[self::F_REQUIRED_MINUTES] = $settings->getMinutesRequired();
        $values[self::F_UPLOAD_REQUIRED] = $settings->isUploadRequired();
    }

    /**
     * @inheritdoc
     */
    public function addInfoProperties(\ilInfoScreenGUI $info, $participant = false, $tutor = false)
    {
        $settings = $this->vc_actions->select();
        if ($participant || $tutor) {
            $info->addProperty($this->txt("csn_settings_phone"), $this->stringToPlaceholder($settings->getPhone()));
            $info->addProperty($this->txt("pin"), $this->stringToPlaceholder($settings->getPin()));
        }

        if ($tutor) {
            $info->addProperty($this->txt("required_minutes"), $this->stringToPlaceholder($settings->getMinutesRequired()));
        }
    }

    /**
     * Returns null if string is empty
     *
     * @param string 	$string
     *
     * @return string | null
     */
    protected function stringToNull($string)
    {
        if ($string == "") {
            return null;
        }

        return $string;
    }

    /**
     * Returns placeholder if string is empty
     *
     * @param string 	$string
     *
     * @return string
     */
    protected function stringToPlaceholder($string)
    {
        if ($string == "") {
            return "-";
        }

        return $string;
    }


    /**
     * @param 	string	$code
     * @return	string
     */
    public function txt($code)
    {
        return $this->object->pluginTxt($code);
    }
}
