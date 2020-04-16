<?php
namespace CaT\Plugins\MaterialList;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;

/**
 * Provide common components for the course.
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
        return [MailingOccasion::class];
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
        assert('is_string($component_type)');
        if ($component_type === MailingOccasion::class) {
            return $this->getMailingOccasions($entity);
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
            $txt = $owner->txtClosure();
            $ret[] = new Mailing\MaterialListOccasion($entity, $owner, $txt);
        }
        return $ret;
    }
}
