<?php
namespace CaT\Plugins\OnlineSeminar;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;

/**
 * When a memberlist is finalized, members should recieve a mail with their status.
 *
 * @author Stefan Hecken <nils.haagen@concepts-and-training.de>
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
        $owners = $this->owners();
        foreach ($owners as $owner) {
            $this->txt = $owner->txtClosure();
            if ($component_type === MailingOccasion::class) {
                return $this->getMailingOccasions($entity, $owner);
            }
            throw new \InvalidArgumentException("Unexpected component type '$component_type'");
        }
        return array();
    }


    /**
     * Returns components for automatic mails
     *
     * @return MailingOccasion[]
     */
    protected function getMailingOccasions($entity, $owner)
    {
        return array(
            new Mailing\NotFinalizedOccasion($entity, $owner, $this->txt)
        );
    }

    /**
     * Parse lang code to text
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt($code)
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
