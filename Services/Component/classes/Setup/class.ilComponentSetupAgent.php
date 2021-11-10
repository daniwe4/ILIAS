<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;

class ilComponentSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

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
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilComponentDefinitionsStoredObjective();
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilComponentDefinitionsStoredObjective(false);
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjective(string $name, Setup\Config $config = null) : Setup\Objective
    {
        if ($name == "installAllPlugins") {
            return new ObjectiveCollection(
                "Ensure all plugins listed in db are installed.",
                false,
                new ilComponentAllPluginsAreInstalledObjective()
            );
        }
        throw new InvalidArgumentException(
            "There is no named objective '$name'"
        );
    }
}
