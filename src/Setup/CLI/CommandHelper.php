<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use Symfony\Component\Console\Input\InputInterface;

trait CommandHelper
{
    protected function getRelevantAgent(InputInterface $input) : Agent
    {
        $agents = $this->agent_finder->getSystemAgents();

        if (!$input->getOption("no-plugins") && is_null($input->getArgument('plugin-name'))) {
            $agents = array_merge($agents, $this->agent_finder->getPluginAgents());

            if ($input->getOption("skip")) {
                $skip_plugins = array_map(function ($a) {
                    return strtolower($a);
                }, $input->getOption("skip"));

                $agents = array_filter($agents, function ($key) use ($skip_plugins) {
                    if (in_array($key, $skip_plugins)) {
                        return false;
                    }
                    return true;
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        if ($input->hasArgument('plugin-name') && !is_null($input->getArgument('plugin-name'))) {
            $agents = array_merge($agents, [$this->agent_finder->getPluginAgent($input->getArgument('plugin-name'))]);
        }

        return $this->agent_finder->buildAgentCollection($agents);
    }
}
