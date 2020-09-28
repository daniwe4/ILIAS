<?php
/* Copyright (c) 2020 Danie Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

interface AgentFinder
{
    /**
     * Collect all agents exclude plugin agents
     *
     * @return Agent[]
     */
    public function getSystemAgents() : array;

    /**
     * Collect all agents in plugin directories
     *
     * @return Agent[]
     */
    public function getPluginAgents() : array;

    /**
     * Get a specific plugin agent.
     *
     * @param string $name Name of the plugin agent. If there is no plugin agent
     *                     this would be the name for a default plugin agent.
     * @return Agent
     */
    public function getPluginAgent(string $name) : Agent;
}
