<?php
namespace CaT\Plugins\RoomSetup;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing as TMSMailing;

/**
* Provide mail-context in global scope.
*
* @author Nils Haagen <nils.haagen@concepts-and-training.de>
*/
class UnboundProvider extends SeparatedUnboundProvider
{
    /**
     * @inheritdocs
     */
    public function componentTypes()
    {
        return [TMSMailing\MailContext::class];
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
        if ($component_type === TMSMailing\MailContext::class) {
            return [new Mailing\MailContextRoomSetup($entity, $owner)];
        }
        return array();
    }
}
