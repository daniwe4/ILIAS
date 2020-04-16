<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjCourseClassification.php";

class ilCourseClassificationImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
            $obj->setTitle((string) $xml->title);
            $obj->setDescription((string) $xml->description);
            $obj->setImportId($a_id);
        } else {
            $obj = new ilObjCourseClassification();
            $obj->setTitle((string) $xml->title);
            $obj->setDescription((string) $xml->description);
            $obj->setImportId($a_id);
            $obj->create();
        }

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/CourseClassification", "xccl", $a_id, $new_id);

        return $new_id;
    }
}
