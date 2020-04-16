<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjCourseMailing.php";

class ilCourseMailingImporter extends ilXmlImporter
{
    /**
     * @var ilObjCourseMailing
     */
    protected $obj;

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
            $this->obj = new ilObjCourseMailing();
            $this->obj->setTitle((string) $xml->title);
            $this->obj->setDescription((string) $xml->description);
            $this->obj->setImportId($a_id);
            $this->obj->create();
        }

        $settings = $this->obj->getSettings();
        $settings = $settings
            ->withDaysInvitation((int) $xml->days_invitation)
            ->withDaysRemindInvitation((int) $xml->days_invitation_reminder)
            ->withPreventMailing($this->stringToBool((string) $xml->prevent_mailing))
        ;

        $this->obj->getActions()->updateSettings($settings);

        $new_id = $this->obj->getId();
        $a_mapping->addMapping("Plugins/CourseMailing", "xcml", $a_id, $new_id);

        return $new_id;
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping)
    {
        $this->obj->update();
    }

    private function stringToBool(string $value) : bool
    {
        if ($value == "true") {
            return true;
        }
        return false;
    }
}
