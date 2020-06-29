<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

use \ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Input;

abstract class PluginAgent implements Agent
{
    /**
     * @var string
     */
    protected $plugin_name;

    public function __construct(string $plugin_name)
    {
        $this->plugin_name = $plugin_name;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getConfigInput(Config $config = null) : Input
    {
        throw new \LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Config $config = null) : Objective
    {
        return new ObjectiveCollection(
            'Complete objectives from Services/Component',
            false,
            new \ilComponentInstallPluginObjective($this->plugin_name),
            new \ilComponentUpdatePluginObjective($this->plugin_name),
            new \ilComponentActivatePluginsObjective($this->plugin_name)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Config $config = null) : Objective
    {
        return new ObjectiveCollection(
            'Complete objectives from Services/Component',
            false,
            new \ilComponentUpdatePluginObjective($this->plugin_name)
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Objective
    {
        return new Objective\NullObjective();
    }
}
