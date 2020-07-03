<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjOnlineSeminar.php";

use CaT\Plugins\OnlineSeminar\Settings\OnlineSeminar;

class ilOnlineSeminarImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
        } else {
            $obj = new ilObjOnlineSeminar();
            $obj->create();
        }

        $obj->setTitle((string) $xml->title);
        $obj->setDescription((string) $xml->description);
        $obj->setImportId($a_id);

        $online_seminar = new OnlineSeminar(
            (int) $obj->getId(),
            (string) $xml->type,
            null,
            null,
            null,
            null,
            false,
            (int) $xml->lp_mode
        );

        $settings_db = $obj->getSettingsDB();
        $settings_db->update($online_seminar);
        $obj->setSettings($online_seminar);
        $obj->getVCActions()->create();
        $obj->update();

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/OnlineSeminar", "xrse", $a_id, $new_id);

        return $new_id;
    }
}
