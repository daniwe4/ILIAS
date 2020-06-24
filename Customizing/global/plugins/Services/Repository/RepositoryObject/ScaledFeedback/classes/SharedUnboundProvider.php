<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */
/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\ScaledFeedback;

use \CaT\Ente\ILIAS\SharedUnboundProvider as Base;
use \CaT\Ente\ILIAS\Entity;
use \ILIAS\TMS\Mailing;
use \CaT\Plugins\ScaledFeedback\Mailing\MailContextScaledFeedback;

class SharedUnboundProvider extends Base
{
    /**
     * @inheritdoc
     */
    public function componentTypes()
    {
        return [Mailing\MailContext::class];
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
        if ($component_type === Mailing\MailContext::class) {
            return [
                new MailContextScaledFeedback($entity, $this->owners())
            ];
        }
        throw new \InvalidArgumentException("Unexpected component type '$component_type'");
    }
}
