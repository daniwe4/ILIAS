<?php

/* Copyright (c) 2018 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

require_once "Services/Export/classes/class.ilXmlImporter.php";
require_once "class.ilObjBookingModalities.php";

use CaT\Plugins\BookingModalities\Settings\Booking\Booking;
use CaT\Plugins\BookingModalities\Settings\Storno\Storno;
use CaT\Plugins\BookingModalities\Settings\Member\Member;
use CaT\Plugins\BookingModalities\Settings\Waitinglist\Waitinglist;

class ilBookingModalitiesImporter extends ilXmlImporter
{
    /**
     * @var bool
     */
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
            $this->booking = $this->obj->getBooking();
            $this->storno = $this->obj->getStorno();
            $this->member = $this->obj->getMember();
            $this->waitinglist = $this->obj->getWaitinglist();
        } else {
            $this->obj = new ilObjBookingModalities();
            $this->obj->setTitle((string) $xml->title);
            $this->obj->setDescription((string) $xml->description);
            $this->obj->setImportId($a_id);
            $this->obj->create();
            $this->booking = new Booking($this->obj->getId());
            $this->storno = new Storno($this->obj->getId());
            $this->member = new Member($this->obj->getId());
            $this->waitinglist = new Waitinglist($this->obj->getId());
            $this->is_new = true;
        }

        $this->booking = $this->booking
            ->withBeginning((int) $xml->booking_beginning)
            ->withDeadline((int) $xml->booking_deadline)
            ->withModus((string) $xml->booking_modus)
            ->withToBeAcknowledged($this->stringToBool((string) $xml->booking_to_be_acknowledge))
            ->withSkipDuplicateCheck($this->stringToBool((string) $xml->booking_skip_duplicate_check))
            ->withHideSuperiorApprove($this->stringToBool((string) $xml->booking_hide_superior_approve))
        ;

        $this->storno = $this->storno
            ->withDeadline((int) $xml->storno_deadline)
            ->withHardDeadline((int) $xml->storno_hard_deadline)
            ->withModus((string) $xml->storno_modus)
            ->withReasonType((string) $xml->storno_reason_type)
            ->withReasonOptional($this->stringToBool((string) $xml->storno_reason_optional))
        ;

        $this->member = $this->member
            ->withMin((int) $xml->member_min)
            ->withMax((int) $xml->member_max)
        ;

        $this->waitinglist = $this->waitinglist
            ->withCancellation((int) $xml->waitinglist_cancellation)
            ->withMax((int) $xml->waitinglist_max)
            ->withModus((string) $xml->waitinglist_modus)
        ;

        $this->obj->setBooking($this->booking);
        $this->obj->setStorno($this->storno);
        $this->obj->setMember($this->member);
        $this->obj->setWaitinglist($this->waitinglist);

        $new_id = $this->obj->getId();
        $a_mapping->addMapping("Plugins/BookingModalities", "xbkm", $a_id, $new_id);

        return $new_id;
    }

    public function finalProcessing($mapping)
    {
        if ($this->is_new) {
            $this->obj->getActions()->updateBooking($this->booking);
            $this->obj->getActions()->updateStorno($this->storno);
            $this->obj->getActions()->updateMember($this->member);
            $this->obj->getActions()->updateWaitinglist($this->waitinglist);
        }
    }

    public function afterContainerImportProcessing(ilImportMapping $mapping)
    {
        $this->obj->update();
    }

    private function stringToBool(string $value) : bool
    {
        if ($value == "true") {
            return true;
        }
        return false;
    }
}
