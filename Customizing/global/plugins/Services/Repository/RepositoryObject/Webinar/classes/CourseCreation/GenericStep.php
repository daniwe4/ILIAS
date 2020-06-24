<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\Webinar\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to show user informations about accomadition and configure the venue
 */
class GenericStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_URL = "f_url";
    const F_PASSWORD = "f_password";
    const F_TUTOR_LOGIN = "f_tutor_login";
    const F_TUTOR_PASSWORD = "tutor_password";
    const F_REQUIRED_MINUTES = "f_required_minutes";
    const F_OBJECT_REF_ID = "ref_id";

    const VC_TYPE = "Generic";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(Entity $entity, $owner)
    {
        $this->entity = $entity;
        $this->owner = $owner;
        $this->txt = $owner->txtClosure();
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("webinar");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("webinar_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $this->addGenericInfos($form);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if (count($data) > 0) {
            $form->setValuesByArray($data);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $title_section = new \ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("generic_settings"));
        $form->addItem($title_section);

        $ti = new \ilNonEditableValueGUI($this->txt("url"), "", true);
        $ti->setValue($data[self::F_URL]);
        $form->addItem($ti);

        $ti = new \ilNonEditableValueGUI($this->txt("password"), "", true);
        $ti->setValue($data[self::F_PASSWORD]);
        $form->addItem($ti);

        $ti = new \ilNonEditableValueGUI($this->txt("tutor_login"), "", true);
        $ti->setValue($data[self::F_TUTOR_LOGIN]);
        $form->addItem($ti);

        $ti = new \ilNonEditableValueGUI($this->txt("tutor_password"), "", true);
        $ti->setValue($data[self::F_TUTOR_PASSWORD]);
        $form->addItem($ti);

        $ti = new \ilNonEditableValueGUI($this->txt("required_minutes"), "", true);
        $ti->setValue($data[self::F_REQUIRED_MINUTES] . " " . $this->txt("minutes"));
        $form->addItem($ti);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $webinar = \ilObjectFactory::getInstanceByRefId((int) $data[self::F_OBJECT_REF_ID]);
        unset($data[self::F_OBJECT_REF_ID]);
        $this->request_builder->addConfigurationFor(
            $webinar,
            [
                self::F_URL => $data[self::F_URL],
                self::F_PASSWORD => $data[self::F_PASSWORD],
                self::F_TUTOR_LOGIN => $data[self::F_TUTOR_LOGIN],
                self::F_TUTOR_PASSWORD => $data[self::F_TUTOR_PASSWORD],
                self::F_REQUIRED_MINUTES => (int) $data[self::F_REQUIRED_MINUTES]
            ]
        );
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $post = $_POST;
        $data = [
            self::F_URL => $post[self::F_URL],
            self::F_PASSWORD => $post[self::F_PASSWORD],
            self::F_TUTOR_LOGIN => $post[self::F_TUTOR_LOGIN],
            self::F_TUTOR_PASSWORD => $post[self::F_TUTOR_PASSWORD],
            self::F_REQUIRED_MINUTES => (int) $post[self::F_REQUIRED_MINUTES],
            self::F_OBJECT_REF_ID => (int) $post[self::F_OBJECT_REF_ID]
        ];

        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 600;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return $this->owner->getSettings()->getVCType() == self::VC_TYPE;
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Adds infos of course classification to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addGenericInfos(\ilPropertyFormGUI $form)
    {
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $settings = $this->owner->getSettings();
        $vc_actions = $this->owner->getVCActions();
        $vc_settings = $vc_actions->select();

        $title_section = new \ilFormSectionHeaderGUI();
        $title_section->setTitle($this->txt("generic_settings"));
        $form->addItem($title_section);

        $ti = new \ilTextInputGUI($this->txt("url"), self::F_URL);
        $ti->setRequired(true);
        $ti->setValue($settings->getUrl());
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("password"), self::F_PASSWORD);
        $ti->setMaxLength(32);
        $ti->setRequired(true);
        $ti->setValue($vc_settings->getPassword());
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("tutor_login"), self::F_TUTOR_LOGIN);
        $ti->setMaxLength(64);
        $ti->setRequired(true);
        $ti->setValue($vc_settings->getTutorLogin());
        $form->addItem($ti);

        $ti = new \ilTextInputGUI($this->txt("tutor_password"), self::F_TUTOR_PASSWORD);
        $ti->setMaxLength(32);
        $ti->setRequired(true);
        $ti->setValue($vc_settings->getTutorPassword());
        $form->addItem($ti);

        $ti = new \ilNumberInputGUI($this->txt("required_minutes"), self::F_REQUIRED_MINUTES);
        $ti->setInfo($this->txt("required_minutes_info"));
        $ti->setRequired(true);
        $ti->setValue($vc_settings->getMinutesRequired());
        $form->addItem($ti);

        $hi = new \ilHiddenInputGUI(self::F_OBJECT_REF_ID);
        $hi->setValue($this->owner->getRefId());
        $form->addItem($hi);
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt(string $id)
    {
        return call_user_func($this->txt, $id);
    }
}
