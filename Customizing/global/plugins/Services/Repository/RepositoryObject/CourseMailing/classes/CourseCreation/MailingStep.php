<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace CaT\Plugins\CourseMailing\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use CaT\Ente\ILIAS\Entity;

/**
 * Step to show configuration table for course mailing
 */
class MailingStep extends \CourseCreationStep
{
    const F_PREVENT_MAILING = "prevent_mailing";
    const TYPE_COPY_SETTINGS = "xcps";

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
        return $this->txt("mailing");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("agenda_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $form->setId("mailing_step");

        $cb = new \ilCheckboxInputGUI($this->txt("settings_prevent_mailing"), self::F_PREVENT_MAILING);
        $cb->setInfo($this->txt("settings_prevent_mailing_info"));
        $form->addItem($cb);

        //add initial data
        $actions = $this->owner->getActions();
        $settings = $actions->getSettings();
        $init_data = array(
            self::F_PREVENT_MAILING => $settings->getPreventMailing(),
        );
        $this->addDataToForm($form, $init_data);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $form->setValuesByArray($data);
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $item = new \ilNonEditableValueGUI($this->txt("settings_prevent_mailing"), "", true);
        if ($data[self::F_PREVENT_MAILING]) {
            $val = $this->txt('prevent_yes');
        } else {
            $val = $this->txt('prevent_no');
        }
        $item->setValue($val);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $this->request_builder->addConfigurationFor(
            $this->owner,
            $data
        );
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = [];
        $post = $_POST;
        foreach ($post as $key => $value) {
            if ($key == "cmd" || $key == "next") {
                continue;
            }
            $data[$key] = (int) $value;
        }
        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 700;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        $copy_settings = $this->getAllChildrenOfByType(
            (int) $this->getEntityRefId(),
            self::TYPE_COPY_SETTINGS
        );

        foreach ($copy_settings as $copy_setting) {
            if (!$copy_setting->getExtendedSettings()->getSuppressMailDelivery()) {
                return false;
            }
        }
        return true;
    }

    protected function getAllChildrenOfByType(int $ref_id, string $search_type) : array
    {
        global $DIC;
        $children = $DIC["tree"]->getSubTree(
            $DIC["tree"]->getNodeData($ref_id),
            false,
            $search_type
        );
        $ret = array();

        foreach ($children as $child) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($child);
        }

        return $ret;
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
    protected function txt($id)
    {
        assert('is_string($id)');
        return call_user_func($this->txt, $id);
    }
}
