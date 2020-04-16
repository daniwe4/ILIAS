<?php

namespace CaT\Plugins\Accomodation;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Booking;
use \ILIAS\TMS\Mailing;
use CaT\Plugins\Accomodation\Mailing\MailContextAccomodations;
use \ILIAS\TMS\ActionBuilder;
use \ILIAS\TMS\File;
use \ILIAS\TMS\FileImpl;

class UnboundProvider extends SeparatedUnboundProvider
{

    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            Booking\SelfBookingStep::class,
            Booking\SuperiorBookingStep::class,
            Mailing\MailContext::class,
            ActionBuilder::class,
            File::class
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
        $txt = $this->owner()->getTxtClosure();
        $accomodation_actions = $this->owner()->getActions();

        if ($component_type === Booking\SelfBookingStep::class) {
            global $DIC;
            return [new Steps\SelfBookAccomodationsStep($entity, $txt, $accomodation_actions, $DIC->user())];
        }
        if ($component_type === Booking\SuperiorBookingStep::class) {
            global $DIC;
            return [new Steps\SuperiorBookAccomodationsStep($entity, $txt, $accomodation_actions, $DIC->user())];
        }
        if ($component_type === Mailing\MailContext::class) {
            return [new MailContextAccomodations($entity, $this->owner())];
        }
        if ($component_type === ActionBuilder::class) {
            return [
                $this->getActionBuilder($entity, $this->owner())
            ];
        }
        if ($component_type === File::class) {
            return $this->getCourseFiles($entity, $this->owner());
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    protected function getActionBuilder(Entity $entity, $owner) : ActionBuilder
    {
        global $DIC;
        $g_user = $DIC->user();

        return new CourseActions\ActionBuilder($entity, $owner, $g_user);
    }

    /**
     * Get all possible files provided by this accomodation-object
     *
     * @return File[]
     */
    protected function getCourseFiles(Entity $entity, \ilObject $owner)
    {
        return array(
            new FileImpl(
                'accomodationlist',
                'application/pdf',
                $entity,
                $owner,
                function ($owner) {
                    return $owner->getActions()->exportAccomodationList();
                }
            )
        );
    }
}
