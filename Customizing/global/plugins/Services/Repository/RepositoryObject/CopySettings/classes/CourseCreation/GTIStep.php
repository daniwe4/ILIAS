<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;
use CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilActions;

/**
 * Step to inform the user about content of training
 */
class GTIStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_CATEGORIES = "categories";
    const F_SET_TRAININGTIMES_MANUALLY = "set_trainingtimes_manually";
    const F_TIME = "time";

    const HH = "hh";
    const MM = "mm";

    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilObjCopySettings
     */
    protected $object;

    public function __construct(Entity $entity, \Closure $txt, \ilObjCopySettings $object)
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
        return $this->txt("training_content");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("training_content_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        $this->addEduTrackingInfos($form, false);

        $tpl = $this->getDIC()->ui()->mainTemplate();
        $js_file = sprintf(
            '%s/templates/gtiStep.js',
            $this->object->getPluginDirectory()
        );
        $tpl->addJavaScript($js_file);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if (count($data) > 0) {
            $values = [];
            $values[self::F_CATEGORIES] = $data[self::F_CATEGORIES];
            $values[self::F_SET_TRAININGTIMES_MANUALLY] = $data[self::F_SET_TRAININGTIMES_MANUALLY];
            if (!is_null($data[self::F_TIME])) {
                $values[self::F_TIME] = $data[self::F_TIME];
            }

            $form->setValuesByArray($values);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $pl = \ilPluginAdmin::getPluginObjectById('xetr');
        $actions = $pl->getConfigActionsFor("GTI");

        $xetr = $this->getEduTracking();
        $txt = $xetr->txtClosure();

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($txt("gti_infos"));
        $form->addItem($sec);

        $item = new \ilNonEditableValueGUI($txt("category"), self::F_CATEGORIES, true);
        $options = $this->getCategoriesOptions($actions);
        $item->setValue($options[$data[self::F_CATEGORIES]]);
        $form->addItem($item);

        $item = new \ilNonEditableValueGUI(
            $txt("manually_set_training_time"),
            self::F_SET_TRAININGTIMES_MANUALLY,
            true
        );

        $set_time_manual = $data[self::F_SET_TRAININGTIMES_MANUALLY] == 1;
        $set_manual = $txt("no");
        if ($set_time_manual) {
            $set_manual = $txt("yes");
        }
        $item->setValue($set_manual);
        $form->addItem($item);

        $time_val = $this->txt('set_by_agenda');
        if ($set_time_manual) {
            $hh = (int) $data[self::F_TIME]["hh"];
            $mm = (int) $data[self::F_TIME]["mm"];

            $time_val = $hh
                . " " . $txt("hours")
                . " " . $mm
                . " " . $txt('minutes');
        }

        $item = new \ilNonEditableValueGUI($txt("training_time"), self::F_TIME, true);
        $item->setValue($time_val);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $pl = \ilPluginAdmin::getPluginObjectById('xetr');
        $gti_actions = $pl->getConfigActionsFor("GTI");
        $settings = $gti_actions->select();
        if ($settings->getAvailable()) {
            $xetr = $this->getEduTracking();
            $time = $data[self::F_TIME]["hh"] * 60 + $data[self::F_TIME]["mm"];
            $data[self::F_TIME] = $time;
            $this->request_builder->addConfigurationFor(
                $xetr,
                ["update_gti" => $data]
            );
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = [];
        $data[self::F_CATEGORIES] = (int) $form->getInput(self::F_CATEGORIES);
        $data[self::F_SET_TRAININGTIMES_MANUALLY] = (int) $form->getInput(self::F_SET_TRAININGTIMES_MANUALLY);
        $data[self::F_TIME] = $form->getInput(self::F_TIME);
        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 375;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        if (!\ilPluginAdmin::isPluginActive('xetr')) {
            return false;
        }

        $pl = \ilPluginAdmin::getPluginObjectById('xetr');
        $actions = $pl->getConfigActionsFor("GTI");
        $settings = $actions->select();
        if (is_null($settings) || !$settings->getAvailable()) {
            return false;
        }

        $xetr = $this->getEduTracking();
        if (is_null($xetr)) {
            return false;
        }

        return $this->object->getExtendedSettings()->isEditGti();
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
     * Adds infos of edu tracking to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addEduTrackingInfos(\ilPropertyFormGUI $form)
    {
        if (!\ilPluginAdmin::isPluginActive('xetr')) {
            return;
        }

        $pl = \ilPluginAdmin::getPluginObjectById('xetr');
        $actions = $pl->getConfigActionsFor("GTI");
        $settings = $actions->select();
        if (!$settings->getAvailable()) {
            return;
        }

        $xetr = $this->getEduTracking();
        if (is_null($xetr)) {
            return;
        }

        $xetr = $this->getEduTracking();
        $txt = $xetr->txtClosure();
        $object_actions = $xetr->getActionsFor("GTI");
        $object_settings = $object_actions->select();

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($txt("gti_infos"));
        $form->addItem($sec);

        $options = array(null => $txt("please_select"), 0 => "-");

        $sb = new \ilSelectInputGUI($txt("category"), self::F_CATEGORIES);
        $sb->setOptions($options + $this->getCategoriesOptions($actions));
        $sb->setRequired(true);
        $sb->setValue($object_settings->getCategoryId());
        $form->addItem($sb);

        $time_manual = $object_settings->getSetTrainingTimeManually();
        $sb = new \ilSelectInputGUI(
            $txt("manually_set_training_time"),
            self::F_SET_TRAININGTIMES_MANUALLY
        );
        $sb->setOptions(array(1 => $txt("yes"), 0 => $txt("no")));
        $sb->setValue((int) $time_manual);
        $sb->setInfo($this->txt("if_not_set_by_agenda"));
        $form->addItem($sb);

        $ni = new \ilTimeInputGUI($txt("training_time"), self::F_TIME);
        $ni->setDisabled(!$time_manual);
        $ni->setMaxHours(250);
        $ni->setMinuteStepSize(5);

        $minutes = $object_settings->getMinutes();
        $hh = $minutes / 60;
        $mm = $minutes - ($hh * 60);

        $hh = str_pad($hh, 2, 0, STR_PAD_LEFT);
        $mm = str_pad($mm, 2, 0, STR_PAD_LEFT);
        $ni->setHours($hh);
        $ni->setMinutes($mm);
        $form->addItem($ni);
    }

    /**
     * Get EduTracking where user as permission to copy
     *
     * @return ilObjEduTracking | null
     */
    protected function getEduTracking()
    {
        $xetrs = $this->getAllChildrenOfByType($this->getEntityRefId(), "xetr");
        $xetrs = array_filter($xetrs, function ($xetr) {
            $xetr_ref_id = $xetr->getRefId();
            return $this->checkAccess(["visible", "read", "copy"], $xetr_ref_id);
        });

        if (count($xetrs) == 0) {
            return null;
        }

        return array_shift($xetrs);
    }

    protected function getCategoriesOptions(ilActions $actions) : array
    {
        $categories_objects = $actions->selectCategories();
        $categories = array();
        foreach ($categories_objects as $cat) {
            $categories[$cat->getId()] = $cat->getName();
        }
        $categories = $this->sortData($categories);

        return $categories;
    }

    private function sortData(array $data) : array
    {
        uasort($data, function (string $a, string $b) {
            return strcasecmp($a, $b);
        });
        return $data;
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
