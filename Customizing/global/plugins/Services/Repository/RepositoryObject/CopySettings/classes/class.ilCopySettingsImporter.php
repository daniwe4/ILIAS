<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjCopySettings.php";

class ilCopySettingsImporter extends ilXmlImporter
{
    /**
     * @var ilObjCopySettings
     */
    protected $obj;

    /**
     * @var \CaT\Plugins\CopySettings\Settings\Settings
     */
    protected $settings;

    /**
     * @var bool
     */
    protected $is_new = false;

    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $this->obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
            $this->obj->setTitle((string) $xml->title);
            $this->obj->setDescription((string) $xml->description);
            $this->obj->setImportId($a_id);
        } else {
            $this->obj = new ilObjCopySettings();
            $this->obj->setTitle((string) $xml->title);
            $this->obj->setDescription((string) $xml->description);
            $this->obj->setImportId($a_id);
            $this->obj->create();
            $this->is_new = true;
        }

        $settings = $this->obj->getSettingsActions()->select();
        $this->settings = $settings
            ->withEditTitle($this->stringToBool((string) $xml->edit_title))
            ->withEditTargetGroups($this->stringToBool((string) $xml->edit_target_groups))
            ->withEditContent($this->stringToBool((string) $xml->edit_content))
            ->withEditBenefits($this->stringToBool((string) $xml->edit_benefits))
            ->withEditIDDLearningTime($this->stringToBool((string) $xml->edit_idd_learningtime))
            ->withRoleIds(unserialize(base64_decode((string) $xml->role_ids)))
            ->withTimeMode((string) $xml->time_mode)
            ->withMinDaysInFuture((int) $xml->min_days_in_future)
            ->withAdditionalInfos($this->stringToBool((string) $xml->additional_infos))
            ->withNoMail($this->stringToBool((string) $xml->no_mail))
            ->withEditProvider($this->stringToBool((string) $xml->edit_provider))
            ->withEditVenue($this->stringToBool((string) $xml->edit_venue))
        ;

        $new_id = $this->obj->getId();
        $a_mapping->addMapping("Plugins/CopySettings", "xcps", $a_id, $new_id);

        return $new_id;
    }

    public function finalProcessing($mapping)
    {
        if ($this->is_new) {
            $actions = $this->obj->getSettingsActions();
            $actions->update($this->settings);
        }
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping)
    {
        $this->obj->update();

        // Its important to update the settings after update,
        // otherwise update overwrites the settings
        $actions = $this->obj->getSettingsActions();
        $actions->update($this->settings);

        $this->updateTitle();
    }

    protected function updateTitle()
    {
        $lng = $this->obj->txtClosure();

        $course = $this->obj->getParentCourse();

        if (!is_null($course)) {
            $title = $course->getTitle();
            $course->setTitle($lng("template_prefix") . ": " . $title);
            $course->update();
        }

        $this->obj->getTemplateCoursesDB()->create(
            (int) $this->obj->getId(),
            (int) $course->getId(),
            (int) $course->getRefId()
        );
    }

    private function stringToBool(string $value) : bool
    {
        if ($value == "true") {
            return true;
        }
        return false;
    }
}
