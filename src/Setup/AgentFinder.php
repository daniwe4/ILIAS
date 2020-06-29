<?php
/* Copyright (c) 2020 Danie Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

interface AgentFinder
{
    /**
     * Collect all files exclude plugin directories ending with SetupAgent.php
     *
     * @return array
     */
    public function getSystemAgents() : array;

    /**
     * Collect all files in plugin directories ending with SetupAgent.php
     *
     * @return array
     */
    public function getPluginAgents() : array;

    /**
     * Get a specific plugin agent.
     *
     * @return Agent
     */
    public function getPluginAgent(string $name) : Agent;
}
