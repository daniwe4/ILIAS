<?php

namespace CaT\Plugins\OnlineSeminar\VC\Generic;

use CaT\Plugins\OnlineSeminar\VC;

/**
 * Deliver and evaluates form settings form values required by vc
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class FormHelperImpl implements VC\FormHelper
{
    const F_PASSWORD = "f_password";
    const F_TUTOR_LOGIN = "f_tutor_login";
    const F_TUTOR_PASSWORD = "tutor_password";
    const F_REQUIRED_MINUTES = "f_required_minutes";

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
        $title_section->setTitle($this->txt("generic_settings"));
        $form->addItem($title_section);

        $ti = new \ilTextInputGUI($this->txt("password"), self::F_PASSWORD);
        $ti->setMaxLength(150);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("tutor_login"), self::F_TUTOR_LOGIN);
        $ti->setMaxLength(64);
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("tutor_password"), self::F_TUTOR_PASSWORD);
        $ti->setMaxLength(32);
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("required_minutes"), self::F_REQUIRED_MINUTES);
        $ti->setInfo($this->txt("required_minutes_info"));
        $form->addItem($ti);
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

        $settings = $settings->withPassword($this->stringToNull($values[self::F_PASSWORD]))
            ->withTutorLogin($this->stringToNull($values[self::F_TUTOR_LOGIN]))
            ->withTutorPassword($this->stringToNull($values[self::F_TUTOR_PASSWORD]))
            ->withMinutesRequired($required_minutes);

        $this->vc_actions->update($settings);
    }

    /**
     * @inheritdoc
     */
    public function getFormValues(array &$values)
    {
        $settings = $this->vc_actions->select();

        $values[self::F_PASSWORD] = $settings->getPassword();
        $values[self::F_TUTOR_LOGIN] = $settings->getTutorLogin();
        $values[self::F_TUTOR_PASSWORD] = $settings->getTutorPassword();
        $values[self::F_REQUIRED_MINUTES] = $settings->getMinutesRequired();
    }

    /**
     * @inheritdoc
     */
    public function addInfoProperties(\ilInfoScreenGUI $info, $participant = false, $tutor = false)
    {
        $settings = $this->vc_actions->select();

        if ($participant || $tutor) {
            $info->addProperty($this->txt("password"), $this->stringToPlaceholder($settings->getPassword()));
        }

        if ($tutor) {
            $info->addProperty($this->txt("tutor_login"), $this->stringToPlaceholder($settings->getTutorLogin()));
            $info->addProperty($this->txt("tutor_password"), $this->stringToPlaceholder($settings->getTutorPassword()));
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
