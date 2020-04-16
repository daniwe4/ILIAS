<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to configure booking modalities
 */
class BookingModalitiesStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_MIN_MEMBER = "min_member";
    const F_MAX_MEMBER = "max_member";

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

    public function __construct(Entity $entity, \Closure $txt, $owner)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->owner = $owner;
    }

    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    /**
     * @inheritdoc
     */
    public function entity()
    {
        return $this->entity;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->txt("label_booking_modalitites");
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->txt("label_booking_modalitites_desc");
    }

    /**
     * @inheritdoc
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $plugin_txt = $this->getPluginTxtClosure();
        $crs = $this->entity()->object();
        $min = '';
        $max = '';
        if ($crs->isSubscriptionMembershipLimited()) {
            $min = $crs->getSubscriptionMinMembers();
            $max = $crs->getSubscriptionMaxMembers();
        }

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("sec_modalities"));
        $form->addItem($sec);

        $item = new \ilNumberInputGUI($plugin_txt("member_min"), self::F_MIN_MEMBER);
        $item->setMinValue(0);
        $item->setValue($min);
        $form->addItem($item);
        $item = new \ilNumberInputGUI($plugin_txt("member_max"), self::F_MAX_MEMBER);
        $item->setMinValue(0);
        $item->setValue($max);
        $form->addItem($item);
    }

    /**
     * @inheritdoc
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $form->setValuesByArray($data);
    }

    /**
     * @inheritdoc
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $plugin_txt = $this->getPluginTxtClosure();
        $min = $data[self::F_MIN_MEMBER];
        $max = $data[self::F_MAX_MEMBER];

        if (!is_numeric($min) || (int) $min === 0) {
            $min = $plugin_txt('no_minimum_places');
        }
        if (!is_numeric($max)) {
            $max = $plugin_txt('infinity_places');
        }

        $item = new \ilNonEditableValueGUI($plugin_txt("member_min"), "", true);
        $item->setValue($min);
        $form->addItem($item);
        $item = new \ilNonEditableValueGUI($plugin_txt("member_max"), "", true);
        $item->setValue($max);
        $form->addItem($item);
    }

    /**
     * @inheritdoc
     */
    public function processStep($data)
    {
        foreach ($this->getBookingModalityObjectsOfCourse() as $booking_obj) {
            $values = [
                \ilObjBookingModalities::COURSE_CREATION_MIN_MEMBERS => $data[self::F_MIN_MEMBER],
                \ilObjBookingModalities::COURSE_CREATION_MAX_MEMBERS => $data[self::F_MAX_MEMBER]
            ];
            $this->request_builder->addConfigurationFor($booking_obj, $values);
        }
    }

    /**
     * @inheritdoc
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $post = $_POST;
        $data = [
            self::F_MIN_MEMBER => $post[self::F_MIN_MEMBER],
            self::F_MAX_MEMBER => $post[self::F_MAX_MEMBER]
        ];
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 190;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable()
    {
        return (
            $this->owner->getExtendedSettings()->getEditMemberlimits()
            && count($this->getBookingModalityObjectsOfCourse()) > 0
        );
    }

    /**
     * @inheritdoc
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdoc
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    protected function txt(string $id) : string
    {
        return call_user_func($this->txt, $id);
    }

    protected function getPluginTxtClosure() : \Closure
    {
        $plug = \ilPluginAdmin::getPluginObjectById('xbkm');
        return $plug->txtClosure();
    }

    protected function getBookingModalityObjectsOfCourse() : array
    {
        $crs = $this->entity()->object();
        return $this->getAllChildrenOfByType($crs->getRefId(), "xbkm");
    }
}
