<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjEduTracking.php";

class ilEduTrackingImporter extends ilXmlImporter
{
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
            $this->obj = new ilObjEduTracking();
            $this->obj->setTitle((string) $xml->title);
            $this->obj->setDescription((string) $xml->description);
            $this->obj->setImportId($a_id);
            $this->obj->create();
            $this->is_new = true;
        }

        $new_id = $this->obj->getId();
        $a_mapping->addMapping("Plugins/EduTracking", "xetr", $a_id, $new_id);

        return $new_id;
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping)
    {
        $this->obj->update();
    }
}
