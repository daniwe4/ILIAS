<?php

namespace CaT\Plugins\BookingApprovals;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Booking;
use \ILIAS\TMS\Cockpit\CockpitItem;
use \ILIAS\TMS\Cockpit\CockpitItemImpl;
use \ILIAS\TMS\CourseAction;

class UnboundProvider extends SeparatedUnboundProvider
{
    /**
    * @inheritdocs
    */
    public function componentTypes()
    {
        return [
            CockpitItem::class
        ];
    }

    /**
    * Build the component(s) of the given type for the given object.
    *
    * @param   string    $component_type
    * @param   Entity    $provider
    * @return  Component[]
    */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        $owner = $this->owner();
        $booking_actions = $owner->getBookingActions();
        $this->txt = $owner->txtClosure();
        global $DIC;
        $this->g_user = $DIC->user();

        if ($component_type === CockpitItem::class) {
            return $this->getCockpitElements($entity);
        }

        if ($component_type === Booking\SuperiorBookingWithApprovalsStep::class) {
            return $this->getSuperiorBookingWithApprovalsSteps(
                $entity,
                $booking_actions,
                $modalities_doc,
                $owner,
                $this->g_user
            );
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    protected function getCockpitElements(Entity $entity)
    {
        $returns = [];
        foreach ($this->owner()->getProvidedValues() as $s) {
            $returns[] = new CockpitItemImpl(
                $entity,
                $s["title"],
                $s["tooltip"],
                $s["link"],
                $s["icon_path"],
                $s["active_icon_path"],
                $s["identifier"]
            );
        }

        return $returns;
    }
}
