<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjWebinar.php";

use CaT\Plugins\Webinar\Settings\Webinar;

class ilWebinarImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
        } else {
            $obj = new ilObjWebinar();
            $obj->create();
        }

        $obj->setTitle((string) $xml->title);
        $obj->setDescription((string) $xml->description);
        $obj->setImportId($a_id);

        $webinar = new Webinar(
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
        $settings_db->update($webinar);
        $obj->setSettings($webinar);
        $obj->getVCActions()->create();
        $obj->update();

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/Webinar", "xrse", $a_id, $new_id);

        return $new_id;
    }
}
