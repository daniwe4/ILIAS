<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\ObjectiveWithPreconditions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\NoConfirmationException;

/**
 * Achieves an objective
 */
class AchieveCommand extends Command
{
    use HasAgent;
    use HasConfigReader;
    use ObjectiveHelper;

    protected static $defaultName = "achieve";

    /**
     * var Objective[]
     */
    protected $preconditions;

    /**
     * @var Objective[] $preconditions will be achieved before command invocation
     */
    public function __construct(AgentFinder $agent_finder, ConfigReader $config_reader, array $preconditions)
    {
        parent::__construct();
        $this->agent_finder = $agent_finder;
        $this->config_reader = $config_reader;
        $this->preconditions = $preconditions;
    }


    public function configure()
    {
        $this->setDescription("Execute a method for an agent to achieve a specific objective.");
        $this->addArgument("agent_method", InputArgument::REQUIRED, "Method to be execute from agent. Format: Agent::Method");
        $this->addArgument("config", InputArgument::OPTIONAL, "Configuration file for the installation");
        $this->addOption("config", null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, "Define fields in the configuration file that should be overwritten, e.g. \"a.b.c=foo\"", []);
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the update.");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        list($class_name, $method) = explode("::", $input->getArgument('agent_method'));

        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Execute " . $method . " on " . $class_name);

        $agent = $this->getRelevantAgent($input);

        $config = null;
        if ($agent->hasConfig()) {
            if (is_null($input->getArgument("config"))) {
                throw new \InvalidArgumentException("Agent '" . $class_name . "' needs a config file.");
            }
            $config = $this->readAgentConfig($agent, $input);
        }

        $objective = $agent->getAchieveObjectives($config, $method);

        if (is_null($objective)) {
            throw new \InvalidArgumentException("Method '" . $method . "' not found for '" . $class_name ."'.");
        }

        if (count($this->preconditions) > 0) {
            $objective = new ObjectiveWithPreconditions(
                $objective,
                ...$this->preconditions
            );
        }

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);
        if ($config) {
            $environment = $this->addAgentConfigsToEnvironment($agent, $config, $environment);
        }

        try {
            $this->achieveObjective($objective, $environment, $io);
            $io->success("Execute '" . $method . "' on '" . $class_name . "'. Thanks and have fun!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborting execute of '" . $method . "' on '" . $class_name . "', a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }
}
