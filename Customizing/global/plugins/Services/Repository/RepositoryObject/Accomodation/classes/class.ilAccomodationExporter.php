<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlExporter.php";
require_once "class.ilObjAccomodation.php";

class ilAccomodationExporter extends ilXmlExporter
{
    public function init()
    {
    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $ref_ids = ilObject::_getAllReferences($a_id);
        $ref_id = array_shift($ref_ids);
        $obj = new ilObjAccomodation((int) $ref_id);

        $writer = new ilXmlWriter();
        $writer->xmlStartTag("xoac");
        $writer->xmlElement("title", null, $obj->getTitle());
        $writer->xmlElement("description", null, $obj->getDescription());
        $writer->xmlEndTag("xoac");

        return $writer->xmlDumpMem(false);
    }

    public function getValidSchemaVersions($a_entity)
    {
        return array(
            "5.3.0" => array(
                "namespace" => "http://www.ilias.de/Plugins/TestRepositoryObject/md/5_3",
                "xsd_file" => "ilias_md_5_3.xsd",
                "min" => "5.3.0",
                "max" => "")
        );
    }
}
