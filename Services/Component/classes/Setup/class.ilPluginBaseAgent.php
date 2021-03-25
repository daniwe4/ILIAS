<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;

abstract class ilPluginBaseAgent implements Setup\Agent
{
    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var Data\Factory
     */
    protected $data_factory;

    /**
     * @var \ilSetupLanguage
     */
    protected $lng;

    final public function __construct(
        Refinery $refinery,
        Data\Factory $data_factory,
        \ilSetupLanguage $lng
    ) {
        $this->refinery = $refinery;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }
}
