<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use CaT\Plugins\CopySettings\Children\Child;
use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\Request;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

/**
 * Step to inform the user about content of training
 */
class CourseInfoStep extends \CourseCreationStep
{
    use ChildAssistant;

    const F_CONTENT = "f_content";
    const F_TARGET_GROUP = "f_target_group";
    const F_TARGET_GROUP_DESC = "f_target_group_desc";
    const F_TITLE = "f_title";
    const F_BENEFITS = "f_benefits";
    const F_IDD_LEARNINGTIME = "f_idd_learningtime";

    const HH = "hh";
    const MM = "mm";

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

    /**
     * @var	\ilObjCopySettings
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
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $extended_settings = $this->object->getExtendedSettings();
        if ($extended_settings->getEditTitle()) {
            $item = new \ilTextInputGUI($this->txt("title"), self::F_TITLE);
            $item->setRequired(true);
            $form->addItem($item);
        } else {
            $item = new \ilNonEditableValueGUI($this->txt("title"), "", true);
            $item->setValue($this->purgeTemplateInTitle(\ilObject::_lookupTitle(\ilObject::_lookupObjId($this->getEntityRefId()))));
            $form->addItem($item);
        }

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("sec_content"));
        $form->addItem($sec);

        $this->addCourseClassificationInfos($form);
        $this->addEduTrackingInfos($form, false);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        $values = [];
        $extended_settings = $this->object->getExtendedSettings();
        if ($extended_settings->getEditTitle()) {
            $values[self::F_TITLE] = $data[self::F_TITLE];
        }
        if ($extended_settings->getEditContent()) {
            $values[self::F_CONTENT] = $data[self::F_CONTENT];
        }
        if ($extended_settings->getEditTargetGroups()) {
            $values[self::F_TARGET_GROUP] = $data[self::F_TARGET_GROUP];
        }
        if ($extended_settings->getEditTargetGroupDescription()) {
            $values[self::F_TARGET_GROUP_DESC] = $data[self::F_TARGET_GROUP_DESC];
        }

        if ($extended_settings->getEditBenefits()) {
            $values[self::F_BENEFITS] = $data[self::F_BENEFITS];
        }
        if (!$this->hasAgendaAsChild()
            && $extended_settings->getEditIDDLearningTime()
        ) {
            $values[self::F_IDD_LEARNINGTIME] = $data[self::F_IDD_LEARNINGTIME];
        }

        if (count($values) > 0) {
            $form->setValuesByArray($values);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $title = $this->purgeTemplateInTitle(\ilObject::_lookupTitle(\ilObject::_lookupObjId($this->getEntityRefId())));
        if (array_key_exists(self::F_TITLE, $data)) {
            $title = $data[self::F_TITLE];
        }
        $item = new \ilNonEditableValueGUI($this->txt("title"), "", true);
        $item->setValue($title);
        $form->addItem($item);

        $this->addCourseClassificationInfos($form, false);
        $this->addEduTrackingInfos($form, true);

        $xetr = $this->getEduTracking();
        $extended_settings = $this->object->getExtendedSettings();
        if (!is_null($xetr) && \ilPluginAdmin::isPluginActive('xetr')) {
            $pl = \ilPluginAdmin::getPluginObjectById('xetr');
            $actions = $pl->getConfigActionsFor("IDD");
            $settings = $actions->select();
            if (!$settings->getAvailable()) {
                return;
            }

            if (!$this->hasAgendaAsChild()
                && $extended_settings->getEditIDDLearningTime()
            ) {
                $item = $form->getItemByPostVar(self::F_IDD_LEARNINGTIME);
                $val = $data[self::F_IDD_LEARNINGTIME];
                $idd_learning_time =
                    str_pad($val[self::HH], 2, "0", STR_PAD_LEFT)
                    . ":"
                    . str_pad($val[self::MM], 2, "0", STR_PAD_LEFT)
                    . " "
                    . $this->txt("hours");
                $item->setValue($idd_learning_time);
            }
        }
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $process_options =
            [ Child::COPY => Request::COPY
            , Child::REFERENCE => Request::LINK
            , Child::NOTHING => Request::SKIP
            ];

        $copy_settings = $this->object->getActions()->select();
        foreach ($copy_settings as $cs) {
            $this->request_builder->setCopyOptionFor(
                $cs->getTargetRefId(),
                $process_options[$cs->getProcessType()]
            );
        }

        $extended_settings = $this->object->getExtendedSettings();
        if (\ilPluginAdmin::isPluginActive('xccl')) {
            $values = [];
            if ($extended_settings->getEditContent()) {
                $values["content"] = $data[self::F_CONTENT];
            }
            if ($extended_settings->getEditTargetGroups()) {
                $values["target_group"] = $data[self::F_TARGET_GROUP];
            }
            if ($extended_settings->getEditTargetGroupDescription()) {
                $values["target_group_desc"] = $data[self::F_TARGET_GROUP_DESC];
            }
            if ($extended_settings->getEditBenefits()) {
                $values["benefits"] = $data[self::F_BENEFITS];
            }

            $xccls = $this->getAllChildrenOfByType($this->getEntityRefId(), "xccl");
            foreach ($xccls as $xccl) {
                if (count($values) > 0) {
                    $this->request_builder->addConfigurationFor(
                        $xccl,
                        $values
                    );
                }

                if ($this->hasAgendaAsChild()) {
                    $this->request_builder->addConfigurationFor(
                        $xccl,
                        ["topics" => true]
                    );
                }
            }
        }

        if ($extended_settings->getEditTitle()) {
            $title = $data[self::F_TITLE];
        } else {
            // TODO: This is only necessary to make it possible to show the title of the training
            // during creation (see ilAssignedTrainingsGUI::getTrainingNameByRequest). There should
            // be some better way to build that title.
            $title = $this->purgeTemplateInTitle($this->entity->object()->getTitle());
        }
        $this->request_builder->addConfigurationFor(
            $this->entity->object(),
            ["title" => $title]
        );

        $xetr = $this->getEduTracking();
        if (!is_null($xetr) && \ilPluginAdmin::isPluginActive('xetr')) {
            $pl = \ilPluginAdmin::getPluginObjectById('xetr');
            $idd_actions = $pl->getConfigActionsFor("IDD");
            $settings = $idd_actions->select();
            if ($settings->getAvailable()) {
                if ($this->hasAgendaAsChild()) {
                    $this->request_builder->addConfigurationFor(
                        $xetr,
                        ["update_idd_time" => true]
                    );
                } else {
                    if ($extended_settings->getEditIDDLearningTime()) {
                        $val = $data[self::F_IDD_LEARNINGTIME];
                        $minutes = (int) $val[self::HH] * 60 + (int) $val[self::MM];
                        $this->request_builder->addConfigurationFor(
                            $xetr,
                            ["set_idd_time" => $minutes]
                        );
                    }
                }
            }
            $gti_actions = $pl->getConfigActionsFor("GTI");
            $settings = $gti_actions->select();
            if ($settings->getAvailable()) {
                $this->request_builder->addConfigurationFor(
                    $xetr,
                    ["update_gti_time" => true]
                );
            }
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = [];
        $post = $_POST;
        $extended_settings = $this->object->getExtendedSettings();
        if ($extended_settings->getEditTitle()) {
            $data[self::F_TITLE] = $post[self::F_TITLE];
        }
        if ($extended_settings->getEditContent()) {
            $data[self::F_CONTENT] = $post[self::F_CONTENT];
        }
        if ($extended_settings->getEditTargetGroups()) {
            $target_groups = $post[self::F_TARGET_GROUP];
            if (is_array($target_groups)) {
                $data[self::F_TARGET_GROUP] = array_map("intval", $target_groups);
            } else {
                $data[self::F_TARGET_GROUP] = array();
            }
        }

        if ($extended_settings->getEditTargetGroupDescription()) {
            $data[self::F_TARGET_GROUP_DESC] = $post[self::F_TARGET_GROUP_DESC];
        }

        if ($extended_settings->getEditBenefits()) {
            $data[self::F_BENEFITS] = $post[self::F_BENEFITS];
        }
        if (!$this->hasAgendaAsChild()
            && $extended_settings->getEditIDDLearningTime()
        ) {
            $data[self::F_IDD_LEARNINGTIME] = $post[self::F_IDD_LEARNINGTIME];
        }
        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 100;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return true;
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
     * @param	bool	$full
     *
     * @return void
     */
    protected function addCourseClassificationInfos(\ilPropertyFormGUI $form, $full = true)
    {
        $xccl = $this->getCourseClassification();
        $extended_settings = $this->object->getExtendedSettings();
        if (!is_null($xccl)) {
            $txt = $xccl->txtClosure();
            $course_classification = $xccl->getCourseClassification();
            $actions = $xccl->getActions();
            $content = (string) $course_classification->getContent();
            $goals = (string) $course_classification->getGoals();

            $types = $this->sortValues($actions->getTypeName($course_classification->getType()));
            $type = implode("<br>", $types);
            $item = new \ilNonEditableValueGUI($txt("type_short"), "", true);
            $item->setValue($type);
            $form->addItem($item);

            $topics = $this->txt("set_by_agenda");
            if (!$this->hasAgendaAsChild()) {
                $names = $this->sortValues($actions->getTopicsNames($course_classification->getTopics()));
                $topics = implode("<br>", $names);
            }
            $item = new \ilNonEditableValueGUI($txt("topics_short"), "", true);
            $item->setValue($topics);
            $form->addItem($item);

            if ($full) {
                if ($extended_settings->getEditContent()) {
                    $ti = new \ilTextAreaInputGUI($txt("content_short"), self::F_CONTENT);
                    $ti->setRows(5);
                    $ti->setRequired(true);
                    $ti->setValue($content);
                    $form->addItem($ti);
                } else {
                    $item = new \ilNonEditableValueGUI($txt("content_short"), "", true);
                    $item->setValue($content);
                    $form->addItem($item);
                }

                if ($extended_settings->getEditTargetGroups()) {
                    require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
                    $ms = new \ilMultiSelectInputGUI($txt("target_groups"), self::F_TARGET_GROUP);
                    $ms->setWidthUnit("%");
                    $ms->setWidth(100);
                    $ms->setHeightUnit("px");
                    $ms->setHeight(93);
                    $targetgroup_options = $this->sortValues($actions->getTargetGroupOptions());
                    $ms->setOptions($targetgroup_options);
                    $ms->setRequired(true);
                    $target_groups = array_keys($actions->getTargetGroupNames($course_classification->getTargetGroup()));
                    $ms->setValue($target_groups);
                    $form->addItem($ms);
                } else {
                    $target_groups = $this->sortValues($actions->getTargetGroupNames($course_classification->getTargetGroup()));
                    $target_groups = implode("<br>", $target_groups);
                    $item = new \ilNonEditableValueGUI($txt("target_groups"), "", true);
                    $item->setValue($target_groups);
                    $form->addItem($item);
                }

                $edit_target_group_description = $course_classification->getTargetGroupDescription();
                if ($extended_settings->getEditTargetGroupDescription()) {
                    $item = new \ilTextAreaInputGUI($txt("target_group_description"), self::F_TARGET_GROUP_DESC);
                    $item->setRows(5);
                } else {
                    $item = new \ilNonEditableValueGUI($txt("target_group_description"), "", true);
                }
                $item->setValue($edit_target_group_description);
                $form->addItem($item);

                if ($extended_settings->getEditBenefits()) {
                    $ti = new \ilTextAreaInputGUI($txt("goals"), self::F_BENEFITS);
                    $ti->setRows(5);
                    $ti->setRequired(true);
                    $ti->setValue($goals);
                    $form->addItem($ti);
                } else {
                    $item = new \ilNonEditableValueGUI($txt("goals"), "", true);
                    $item->setValue($goals);
                    $form->addItem($item);
                }
            }
        }
    }

    /**
     * String case sort of values keeping keys
     *
     * @param string[] 	$values
     *
     * @return string[]
     */
    protected function sortValues(array $values)
    {
        uasort($values, function ($a, $b) {
            return strcasecmp($a, $b);
        });
        return $values;
    }

    /**
     * Adds infos of edu tracking to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addEduTrackingInfos(\ilPropertyFormGUI $form, $overview)
    {
        $xetr = $this->getEduTracking();
        $xage = $this->getAgenda();

        if (!is_null($xetr) && \ilPluginAdmin::isPluginActive('xetr')) {
            $txt = $xetr->txtClosure();
            $pl = \ilPluginAdmin::getPluginObjectById('xetr');
            $actions = $pl->getConfigActionsFor("IDD");
            $settings = $actions->select();
            if (!$settings->getAvailable()) {
                return;
            }

            if ($this->hasAgendaAsChild()) {
                $idd_learning_time = $this->txt("set_by_agenda");
                $item = new \ilNonEditableValueGUI($txt("learning_time"), "", true);
                $item->setValue($idd_learning_time);
                $form->addItem($item);
            } else {
                $extended_settings = $this->object->getExtendedSettings();
                if ($extended_settings->getEditIDDLearningTime()) {
                    if (!$overview) {
                        require_once("Services/Form/classes/class.ilTimeInputGUI.php");
                        $ni = new \ilTimeInputGUI($this->txt("idd_learningtime"), self::F_IDD_LEARNINGTIME);
                        $ni->setMaxHours(250);
                        $ni->setMinuteStepSize(5);
                        $form->addItem($ni);
                    } else {
                        $item = new \ilNonEditableValueGUI($txt("learning_time"), self::F_IDD_LEARNINGTIME, true);
                        $item->setValue($idd_learning_time);
                        $form->addItem($item);
                    }
                } else {
                    $idd_actions = $xetr->getActionsFor("IDD");
                    $idd_settings = $idd_actions->select();
                    $minutes = $idd_settings->getMinutes();

                    $idd_learning_time = "-";
                    if ($minutes !== null) {
                        $hours = floor($minutes / 60);
                        $minutes = $minutes - $hours * 60;
                        $idd_learning_time =
                            str_pad($hours, 2, "0", STR_PAD_LEFT)
                            . ":"
                            . str_pad($minutes, 2, "0", STR_PAD_LEFT)
                            . " "
                            . $txt("hours");
                    }

                    $item = new \ilNonEditableValueGUI($txt("learning_time"), "", true);
                    $item->setValue($idd_learning_time);
                    $form->addItem($item);
                }
            }
        }
    }

    /**
     * Get CourseClassification where user as permission to copy
     *
     * @return ilObjCourseClassification | null
     */
    protected function getCourseClassification()
    {
        $xccls = $this->getAllChildrenOfByType($this->getEntityRefId(), "xccl");
        $xccls = array_filter($xccls, function ($xccl) {
            $xccl_ref_id = $xccl->getRefId();
            return $this->checkAccess(["visible", "read", "copy"], $xccl_ref_id);
        });

        if (count($xccls) == 0) {
            return null;
        }

        return array_shift($xccls);
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

    /**
     * Get EduTracking where user as permission to copy
     *
     * @return ilObjEduTracking | null
     */
    protected function getAgenda()
    {
        $xages = $this->getAllChildrenOfByType($this->getEntityRefId(), "xage");

        if (count($xages) == 0) {
            return null;
        }

        return array_shift($xages);
    }

    protected function hasAgendaAsChild()
    {
        return is_null($this->getAgenda()) === false;
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

    /**
     * @param	string	$title
     * @return	string
     */
    protected function purgeTemplateInTitle($title)
    {
        $matches = [];
        if (preg_match("/^[^:]*:(.*)$/", $title, $matches)) {
            return $matches[1];
        }
        return $title;
    }
}
