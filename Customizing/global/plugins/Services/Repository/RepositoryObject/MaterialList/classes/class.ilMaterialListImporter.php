<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjMaterialList.php";

class ilMaterialListImporter extends ilXmlImporter
{
    public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
    {
        global $DIC;

        $xml = simplexml_load_string($a_xml);

        if ($new_id = $a_mapping->getMapping("Services/Container", "objs", $a_id)) {
            $refs = ilObject::_getAllReferences($new_id);
            $obj = ilObjectFactory::getInstanceByRefId(end($refs), false);
        } else {
            $obj = new ilObjMaterialList();
            $obj->create();
        }

        $obj->setTitle((string) $xml->title);
        $obj->setDescription((string) $xml->description);
        $obj->setImportId($a_id);

        $settings = new CaT\Plugins\MaterialList\Settings\MaterialList(
            (int) $obj->getId(),
            new ilDateTime(),
            (int) $DIC->user()->getId(),
            $obj->getActions()->getDefaultRecipientMode()
        );

        $obj->setSettings($settings);
        $obj->update();

        $new_id = $obj->getId();
        $a_mapping->addMapping("Plugins/MaterialList", "xmat", $a_id, $new_id);

        return $new_id;
    }
}
