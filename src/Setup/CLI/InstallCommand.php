<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Installation command.
 */
class InstallCommand extends BaseCommand
{
    protected static $defaultName = "install";

    public function configure()
    {
        parent::configure();
        $this->setDescription("Creates a fresh ILIAS installation based on the config");
        $this->addArgument('plugin_name', InputArgument::OPTIONAL, 'Name of the plugin to install.');
        $this->addOption("no_plugins", null, InputOption::VALUE_NONE, "Ignore plugins");
    }

    protected function printIntroMessage(IOWrapper $io)
    {
        $io->title("Installing ILIAS");
    }

    protected function printOutroMessage(IOWrapper $io)
    {
        $io->success("Installation complete. Thanks and have fun!");
    }

    protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io) : Environment
    {
        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        if ($agent instanceof AgentCollection && $config) {
            foreach ($config->getKeys() as $k) {
                $environment = $environment->withConfigFor($k, $config->getConfig($k));
            }
        }

        return $environment;
    }

    protected function getObjective(Agent $agent, ?Config $config) : Objective
    {
        return new ObjectiveCollection(
            "Install and update ILIAS",
            false,
            $agent->getInstallObjective($config)
        );
    }

    protected function getRelevantAgent(InputInterface $input) : Agent
    {
        $agents = $this->agent_finder->getSystemAgents();

        if (!$input->getOption("no_plugins") && is_null($input->getArgument('plugin_name'))) {
            $agents = array_merge($agents, $this->agent_finder->getPluginAgents());
        }

        if ($input->hasArgument('plugin_name') && !is_null($input->getArgument('plugin_name'))) {
            $agents = array_merge($agents, [$this->agent_finder->getPluginAgent($input->getArgument('plugin_name'))]);
        }

        return $this->agent_finder->buildAgentCollection($agents);
    }
}
