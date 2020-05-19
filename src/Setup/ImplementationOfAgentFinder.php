<?php

/* Copyright (c) 2020 Danie Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilSetupAgent;
use ilSetupLanguage;

class ImplementationOfAgentFinder implements AgentFinder
{
    /**
     * @var string
     */
    protected $interface;

    /**
     * @var FieldFactory
     */
    protected $field_factory;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var ilSetupAgent
     */
    protected $common_agent;

    /**
     * @var ilSetupLanguage
     */
    protected $lng;

    /**
     * @var array
     */
    private $ignore = [
        '.*/libs/',
        '.*/test/',
        '.*/tests/',
        '.*/setup/',
        // Classes using removed Auth-class from PEAR
        '.*ilSOAPAuth.*',
        // Classes using unknown
        '.*ilPDExternalFeedBlockGUI.*',
    ];

    public function __construct(
        string $interface,
        FieldFactory $field_factory,
        Refinery $refinery,
        ilSetupAgent $common_agent,
        ilSetupLanguage $lng
    ) {
        $this->interface = $interface;
        $this->field_factory = $field_factory;
        $this->refinery = $refinery;
        $this->common_agent = $common_agent;
        $this->lng = $lng;
    }

    public function getSystemAgents() : AgentCollection
    {
        $ignore = $this->ignore;
        array_unshift($ignore, '.*/plugins/');

        $class_names = $this->getMatchingClassNames($ignore);
        $agents = $this->getAgentsByClassNames($class_names);

        return new AgentCollection($this->field_factory, $this->refinery, $agents);
    }

    public function getPluginAgents() : AgentCollection
    {
        $ignore = $this->ignore;
        array_unshift($ignore, '.*/Modules/', '^/Services/');

        $class_names = $this->getMatchingClassNames($ignore);
        $agents = $this->getAgentsByClassNames($class_names);
        unset($agents['common']);

        return new AgentCollection($this->field_factory, $this->refinery, $agents);
    }

    public function getPluginAgent(string $name) : AgentCollection
    {
        $ignore = $this->ignore;
        array_unshift($ignore, '.*/Modules/', '^/Services/');

        $class_names = $this->getMatchingClassNames($ignore);
        $agents = $this->getAgentsByClassNames($class_names);

        $relevant_agents = [];
        $relevant_agents['common'] = $agents['common'];
        foreach ($agents as $plugin_agent) {
            if (strpos(get_class($plugin_agent), $name) != 0) {
                $relevant_agents[] = $plugin_agent;
            }
        }

        return new AgentCollection($this->field_factory, $this->refinery, $relevant_agents);
    }

    private function getAllClassNames(array $ignore) : \Iterator
    {
        // We use the composer classmap ATM
        $composer_classmap = include "./libs/composer/vendor/composer/autoload_classmap.php";
        $root = substr(__FILE__, 0, strpos(__FILE__, "/src"));

        if (!is_array($composer_classmap)) {
            throw new \LogicException("Composer ClassMap not loaded");
        }

        $regexp = implode(
            "|",
            array_map(
            // fix path-separators to respect windows' backspaces.
                function ($v) {
                    return "(" . str_replace('/', '(/|\\\\)', $v) . ")";
                },
                $ignore
            )
        );

        foreach ($composer_classmap as $class_name => $file_path) {
            $path = str_replace($root, "", realpath($file_path));
            if (!preg_match("#^" . $regexp . "$#", $path)) {
                yield $class_name;
            }
        }
    }

    private function getMatchingClassNames(array $ignore) : \Iterator
    {
        foreach ($this->getAllClassNames($ignore) as $class_name) {
            try {
                $r = new \ReflectionClass($class_name);
                if ($r->isInstantiable() && $r->implementsInterface($this->interface)) {
                    yield $class_name;
                }
            } catch (\Throwable $e) {
                // noting to do here
            }
        }
    }

    private function getAgentsByClassNames(\Iterator $class_names) : array
    {
        $agents["common"] = $this->common_agent;
        foreach ($class_names as $cls) {
            if (preg_match("/ILIAS\\\\Setup\\\\.*/", $cls)) {
                continue;
            }
            $name = get_agent_name_by_class($cls);
            if (isset($agents[$name])) {
                throw new \RuntimeException(
                    "Encountered duplicate agent $name in $cls"
                );
            }
            $agents[strtolower($name)] = new $cls(
                $this->refinery,
                $this->field_factory,
                $this->lng
            );
        };

        return $agents;
    }
}
