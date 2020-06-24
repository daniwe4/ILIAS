<?php

namespace CaT\Plugins\TrainingAdminOverview;

use \CaT\Ente\ILIAS\SeparatedUnboundProvider;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Cockpit\CockpitItem;
use \ILIAS\TMS\Cockpit\CockpitItemImpl;

/**
 * Unbound provider to get informations about this via ente
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class UnboundProvider extends SeparatedUnboundProvider
{
    /**
    * @inheritdocs
    */
    public function componentTypes()
    {
        return [CockpitItem::class];
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
        if ($component_type === CockpitItem::class) {
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

        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
