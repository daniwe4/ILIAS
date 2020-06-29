<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Update command.
 */
class UpdateCommand extends BaseCommand
{
    protected static $defaultName = "update";

    public function configure()
    {
        parent::configure();
        $this
            ->setDescription("Updates an existing ILIAS installation")
            ->addOption("ignore-db-update-messages", null, InputOption::VALUE_NONE, "Ignore messages from the database update steps.");
        $this->addArgument('plugin_name', InputArgument::OPTIONAL, 'Name of the plugin to update.');
        $this->addOption("no_plugins", null, InputOption::VALUE_NONE, "Ignore plugins");
    }

    protected function printIntroMessage(IOWrapper $io)
    {
        $io->title("Updating ILIAS");
    }

    protected function printOutroMessage(IOWrapper $io)
    {
        $io->success("Update complete. Thanks and have fun!");
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
            "Update ILIAS",
            false,
            $agent->getUpdateObjective($config)
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
