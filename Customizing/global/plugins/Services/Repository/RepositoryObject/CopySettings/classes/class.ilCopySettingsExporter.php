<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlExporter.php";
require_once "class.ilObjCopySettings.php";

class ilCopySettingsExporter extends ilXmlExporter
{
    public function init()
    {
    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $ref_ids = ilObject::_getAllReferences($a_id);
        $ref_id = array_shift($ref_ids);
        $obj = new ilObjCopySettings((int) $ref_id);
        $settings = $obj->getSettingsActions()->select();

        $writer = new ilXmlWriter();
        $writer->xmlStartTag("xcps");
        $writer->xmlElement("title", null, $obj->getTitle());
        $writer->xmlElement("description", null, $obj->getDescription());
        $writer->xmlElement("edit_title", null, $this->boolToString($settings->getEditTitle()));
        $writer->xmlElement("edit_target_groups", null, $this->boolToString($settings->getEditTargetGroups()));
        $writer->xmlElement("edit_target_groupdescription", null, $this->boolToString($settings->getEditTargetGroupDescription()));
        $writer->xmlElement("edit_content", null, $this->boolToString($settings->getEditContent()));
        $writer->xmlElement("edit_benefits", null, $this->boolToString($settings->getEditBenefits()));
        $writer->xmlElement("edit_idd_learningtime", null, $this->boolToString($settings->getEditIDDLearningTime()));
        $writer->xmlElement("role_ids", null, base64_encode(serialize($settings->getRoleIds())));
        $writer->xmlElement("time_mode", null, $settings->getTimeMode());
        $writer->xmlElement("min_days_in_future", null, $settings->getMinDaysInFuture());
        $writer->xmlElement("additional_infos", null, $this->boolToString($settings->getAdditionalInfos()));
        $writer->xmlElement("no_mail", null, $this->boolToString($settings->getNoMail()));
        $writer->xmlElement("edit_provider", null, $this->boolToString($settings->getEditProvider()));
        $writer->xmlElement("edit_venue", null, $this->boolToString($settings->getEditVenue()));
        $writer->xmlEndTag("xcps");

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
