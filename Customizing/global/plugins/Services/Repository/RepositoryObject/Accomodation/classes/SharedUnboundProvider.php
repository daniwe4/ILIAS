<?php
namespace CaT\Plugins\Accomodation;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\CourseCreation as CC;

/**
 * Provide mailing occasions for accomodations
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class SharedUnboundProvider extends Base
{
    /**
     * @inheritdoc
     */
    public function componentTypes()
    {
        return [
            MailingOccasion::class,
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
        assert(is_string($component_type));
        if ($component_type === MailingOccasion::class) {
            return $this->getMailingOccasions($entity);
        }
        if ($component_type === CC\Step::class) {
            $owners = $this->owners();
            $owner = array_shift($owners);
            return [
                new CourseCreation\AccomodationStep($entity, $owner->getTxtClosure(), $owner->getActions())
            ];
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }

    /**
     * Returns components for automatic mails
     *
     * @param 	Entity 	$entity
     * @return  MailingOccasion[]
     */
    protected function getMailingOccasions(Entity $entity)
    {
        $ret = array();
        foreach ($this->owners() as $owner) {
            $txt = $owner->getTxtClosure();
            $ret[] = new Mailing\AccomodationListOccasion($entity, $owner, $txt);
        }
        return $ret;
    }
}
