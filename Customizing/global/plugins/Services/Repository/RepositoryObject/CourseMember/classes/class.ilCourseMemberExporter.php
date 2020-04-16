<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlExporter.php";
require_once "class.ilObjCourseMember.php";

class ilCourseMemberExporter extends ilXmlExporter
{
    public function init()
    {
    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $ref_ids = ilObject::_getAllReferences($a_id);
        $ref_id = array_shift($ref_ids);
        $obj = new ilObjCourseMember((int) $ref_id);
        $settings = $obj->getSettings();

        $writer = new ilXmlWriter();
        $writer->xmlStartTag("xcmb");
        $writer->xmlElement("title", null, $obj->getTitle());
        $writer->xmlElement("description", null, $obj->getDescription());
        $writer->xmlElement("credits", null, (string) $settings->getCredits());
        $writer->xmlElement("lp_mode", null, $settings->getLPMode());
        $writer->xmlElement("list_required", null, $this->boolToString($settings->getListRequired()));
        $writer->xmlElement("opt_orgu", null, $this->boolToString($settings->getListOptionOrgu()));
        $writer->xmlElement("opt_text", null, $this->boolToString($settings->getListOptionText()));
        $writer->xmlEndTag("xcmb");

        return $writer->xmlDumpMem(false);
    }

    private function boolToString(bool $value) : string
    {
        if ($value) {
            return "true";
        }
        return "false";
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
