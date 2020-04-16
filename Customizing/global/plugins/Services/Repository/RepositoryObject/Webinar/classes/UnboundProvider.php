<?php

namespace CaT\Plugins\Webinar;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing;
use \ILIAS\TMS\ActionBuilder;
use \ILIAS\TMS\CourseCreation as CC;

class UnboundProvider extends SeparatedUnboundProvider
{

    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [
            Mailing\MailContext::class,
            ActionBuilder::class ,
            CC\Step::class
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
        if ($component_type === Mailing\MailContext::class) {
            return [new MailContextWebinar($entity, $this->owner())];
        }

        if ($component_type === ActionBuilder::class) {
            return [
                $this->getActionBuilder($entity, $this->owner())
            ];
        }

        if ($component_type === CC\Step::class) {
            return $this->getCourseCreationSteps($entity, $this->owner());
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
     * Get all possible course creation steps
     *
     * @param Entity 	$entity
     * @param \ilObjWebinar 	$owner
     *
     * @return CC\Step[]
     */
    protected function getCourseCreationSteps(Entity $entity, \ilObjWebinar $owner)
    {
        return [
            new CourseCreation\GenericStep($entity, $owner),
            new CourseCreation\CSNStep($entity, $owner)
        ];
    }
}
