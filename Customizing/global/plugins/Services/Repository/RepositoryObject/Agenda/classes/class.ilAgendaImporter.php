<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "class.ilObjAgenda.php";

use CaT\Plugins\Agenda\Settings\Settings;

class ilAgendaImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
        } else {
            $obj = new ilObjAgenda();
            $obj->create();
        }

        $obj->setTitle((string) $xml->title);
        $obj->setDescription((string) $xml->description);
        $obj->setImportId($a_id);

        $start_time = new DateTime((string) $xml->start_time, new DateTimeZone("Europe/Berlin"));
        $fnc = function (Settings $s) use ($start_time) {
            return $s->withStartTime($start_time);
        };

        $obj->updateSettings($fnc);
        $obj->update();

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/Agenda", "xage", $a_id, $new_id);

        return $new_id;
    }
}
