<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjCourseMember.php";

use CaT\Plugins\CourseMember\Settings\CourseMemberSettings;

class ilCourseMemberImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
        } else {
            $obj = new ilObjCourseMember();
            $obj->create();
        }

        $obj->setTitle((string) $xml->title);
        $obj->setDescription((string) $xml->description);
        $obj->setImportId($a_id);

        $settings = new CourseMemberSettings((int) $obj->getId());
        $settings = $settings
            ->withCredits((string) $xml->credits)
            ->withLPMode((int) $xml->lp_mode)
            ->withListRequired($this->stringToBool((string) $xml->list_required))
            ->withListOptionOrgu($this->stringToBool((string) $xml->opt_orgu))
            ->withListOptionText($this->stringToBool((string) $xml->opt_text))
        ;

        $obj->setSettings($settings);
        $obj->update();

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/CourseMember", "xcmb", $a_id, $new_id);

        return $new_id;
    }

    private function stringToBool(string $value) : bool
    {
        if ($value == "true") {
            return true;
        }
        return false;
    }
}
