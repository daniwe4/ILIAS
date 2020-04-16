<?php

namespace CaT\Plugins\CopySettings;

use CaT\Plugins\CopySettings\CourseCreation as CreationSteps ;
use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\CourseCreation;

/**
 * Ente-Provider for Courses with the copy settings object.
 */
class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [CourseCreation\Step::class];
    }

    /**
     * Build the component(s) of the given type for the given object.
     *
     * @param   string    $component_type
     * @param   Entity    $provider
     * @throws  \InvalidArgumentException	if $component_type is unknown
     * @return  Component[]
     */
    public function buildComponentsOf($component_type, Entity $entity)
    {
        assert('is_string($component_type)');

        global $DIC;
        $g_user = $DIC["ilUser"];

        if ($component_type === CourseCreation\Step::class) {
            $file_storage = new CreationSteps\AdditionInfoFileStorage($g_user->getId());
            return [
                new CreationSteps\DestinationStep($entity, $this->owner()->txtClosure(), $this->owner()),
                new CreationSteps\CourseInfoStep($entity, $this->owner()->txtClosure(), $this->owner()),
                new CreationSteps\BookingModalitiesStep($entity, $this->owner()->txtClosure(), $this->owner()),
                new CreationSteps\CoursePeriodStep($entity, $this->owner()->txtClosure(), $this->owner()),
                new CreationSteps\CourseOrganisationStep($entity, $this->owner()->txtClosure(), $this->owner()),
                new CreationSteps\AdditionalInfosStep($entity, $this->owner()->txtClosure(), $this->owner(), $file_storage, $g_user),
                new CreationSteps\GTIStep($entity, $this->owner()->txtClosure(), $this->owner())
            ];
        }

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
