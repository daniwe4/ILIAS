<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;

class ilTreeExistsObjective implements Setup\Objective
{
    public function getHash() : string
    {
        return hash("sha256", self::class);
    }

    public function getLabel() : string
    {
        return "The tree exists";
    }

    public function isNotable() : bool
    {
        return true;
    }

    public function getPreconditions(Setup\Environment $environment) : array
    {
        return [
            new ilIniFilesPopulatedObjective()
        ];
    }

    public function achieve(Setup\Environment $environment) : Setup\Environment
    {
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);
        $root_folder_id = $client_ini->readVariable('system', 'ROOT_FOLDER_ID');
        $tree = new ilTree($root_folder_id);

        return $environment->withResource(Setup\Environment::RESOURCE_TREE, $tree->getTreeImplementation());
    }

    public function isApplicable(Setup\Environment $environment) : bool
    {
        return true;
    }
}
