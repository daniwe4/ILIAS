<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlExporter.php";
require_once "class.ilObjBookingModalities.php";

class ilBookingModalitiesExporter extends ilXmlExporter
{
    public function init()
    {
    }

    public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
    {
        $ref_ids = ilObject::_getAllReferences($a_id);
        $ref_id = array_shift($ref_ids);
        $obj = new ilObjBookingModalities((int) $ref_id);

        $booking = $obj->getBooking();
        $storno = $obj->getStorno();
        $member = $obj->getMember();
        $waitinglist = $obj->getWaitinglist();

        $writer = new ilXmlWriter();
        $writer->xmlStartTag("xbkm");
        $writer->xmlElement("title", null, $obj->getTitle());
        $writer->xmlElement("description", null, $obj->getDescription());

        // booking settings
        $writer->xmlElement("booking_beginning", null, $booking->getBeginning());
        $writer->xmlElement("booking_deadline", null, $booking->getDeadline());
        $writer->xmlElement("booking_modus", null, $booking->getModus());
        $writer->xmlElement("booking_to_be_acknowledge", null, $this->boolToString($booking->getToBeAcknowledged()));
        $writer->xmlElement("booking_skip_duplicate_check", null, $this->boolToString($booking->getSkipDuplicateCheck()));
        $writer->xmlElement("booking_hide_superior_approve", null, $this->boolToString($booking->getHideSuperiorApprove()));

        // storno settings
        $writer->xmlElement("storno_deadline", null, $storno->getDeadline());
        $writer->xmlElement("storno_hard_deadline", null, $storno->getHardDeadline());
        $writer->xmlElement("storno_modus", null, $storno->getModus());
        $writer->xmlElement("storno_reason_type", null, $storno->getReasonType());
        $writer->xmlElement("storno_reason_optional", null, $this->boolToString($storno->getReasonOptional()));

        // member settings
        $writer->xmlElement("member_min", null, $member->getMin());
        $writer->xmlElement("member_max", null, $member->getMax());

        // waitinglist settings
        $writer->xmlElement("waitinglist_cancellation", null, $waitinglist->getCancellation());
        $writer->xmlElement("waitinglist_max", null, $waitinglist->getMax());
        $writer->xmlElement("waitinglist_modus", null, $waitinglist->getModus());

        $writer->xmlEndTag("xbkm");

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
