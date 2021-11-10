<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;

class ilLanguageInitializedObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The language object is initialized.";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        if (!$environment->hasConfigFor("language")) {
            return [];
        }

        $config = $environment->getConfigFor("language");
        return [
            new ilLanguagesInstalledAndUpdatedObjective($config, new ilSetupLanguage('en'))
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
