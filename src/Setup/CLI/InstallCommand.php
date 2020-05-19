<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\AchievementTracker;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\NoConfirmationException;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveIterator;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use Prophecy\Argument;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $agents = [];
        $agents[] = $this->agent_finder->getSystemAgents();

        if (!$input->getOption("no_plugins")) {
            $agents[] = $this->agent_finder->getPluginAgents();
        }

        if ($input->hasArgument('plugin_name') && !is_null($input->getArgument('plugin_name'))) {
            $agents = [$this->agent_finder->getPluginAgent($input->getArgument('plugin_name'))];
        }

        // ATTENTION: This is a hack to get around the usage of the echo/exit pattern in
        // the setup for the command line version of the setup. Do not use this.
        define("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES", true);

        $io = new IOWrapper($input, $output, $this->shouldSayYes($input));

        $this->printLicenseMessage($io, $input);

        $this->printIntroMessage($io);

        foreach ($agents as $agent) {
            $config = $this->readAgentConfig($agent, $input);
            $environment = $this->buildEnvironment($agent, $config, $io);
            $goal = $this->getObjective($agent, $config);
            if (count($this->preconditions) > 0) {
                $goal = new ObjectiveWithPreconditions(
                    $goal,
                    ...$this->preconditions
                );
            }
            $goals = new ObjectiveIterator($environment, $goal);

            try {
                while ($goals->valid()) {
                    $current = $goals->current();
                    if ($current->isAchieved($environment)) {
                        $goals->next();
                        continue;
                    }
                    $io->startObjective($current->getLabel(), $current->isNotable());
                    try {
                        $environment = $current->achieve($environment);
                        $io->finishedLastObjective();
                        $goals->setEnvironment($environment);
                    } catch (UnachievableException $e) {
                        $goals->markAsFailed($current);
                        $io->error($e->getMessage());
                        $io->failedLastObjective();
                    }
                    $goals->next();
                }
                $this->printOutroMessage($io);
            } catch (NoConfirmationException $e) {
                $io->error("Aborting Setup, a necessary confirmation is missing:\n\n" .
                           $e->getRequestedConfirmation());
            }
        }
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
            $agent->getInstallObjective($config),
            $agent->getUpdateObjective($config)
        );
    }
}
