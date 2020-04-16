<?php
namespace CaT\Plugins\RoomSetup;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing\MailingOccasion;

/**
 * Mailing-Occasions for deferred events
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
        $owners = $this->owners();
        $ret = array();
        foreach ($owners as $owner) {
            $this->txt = $owner->txtClosure();
            if ($component_type === MailingOccasion::class) {
                $ret = array_merge($ret, $this->getMailingOccasions($entity, $owner));
            }
        }
        return $ret;
    }


    /**
     * Returns components for automatic mails
     *
     * @return MailingOccasion[]
     */
    protected function getMailingOccasions($entity, $owner)
    {
        return array(
            new Mailing\RoomServiceOccasion($entity, $owner, $this->txt),
            new Mailing\RoomSetupOccasion($entity, $owner, $this->txt)
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
