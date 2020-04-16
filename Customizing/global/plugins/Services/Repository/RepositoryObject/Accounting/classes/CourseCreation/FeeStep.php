<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\Accounting\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to show user informations about accomodation and configure the venue
 */
class FeeStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_FEE = "fee";

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

    public function __construct(Entity $entity, \Closure $txt, \ilObjAccounting $object)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->object = $object;
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
        return $this->txt("configure_fee");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("fee_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("configure_fee"));
        $form->addItem($sec);

        $fee = $this->object->getObjectActions()->getFeeActions()->select();
        $item = new \ilNumberInputGUI(
            $this->txt("fee_value"),
            self::F_FEE
        );
        $item->setDecimals(2);
        $item->allowDecimals(true);
        $item->setValue($fee->getFee());
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if (count($data) > 0) {
            $values = array();
            $values[self::F_FEE] = $data[self::F_FEE];
            $form->setValuesByArray($values);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $item = new \ilNonEditableValueGUI($this->txt("fee_value"), "", true);
        $fee = number_format($data[self::F_FEE], 2, ",", "");
        if (strpos($fee, ",") === false) {
            $fee .= ",00";
        }
        $item->setValue($fee);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $this->request_builder->addConfigurationFor(
            $this->object,
            [
                self::F_FEE => $data[self::F_FEE]
            ]
        );
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $post = $_POST;
        $data = [];
        $fee = 0;
        if ($post[self::F_FEE] != "") {
            $fee = $post[self::F_FEE];
            $fee = str_replace(",", ".", $fee);
        }
        $data[self::F_FEE] = (float) $fee;

        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 350;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return $this->object->getSettings()->getEditFee();
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
        return call_user_func($this->txt, $id);
    }
}
