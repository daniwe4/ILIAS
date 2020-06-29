<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilSetupLanguage;

class ImplementationOfAgentFinder implements AgentFinder
{
    /**
     * @var FieldFactory|null
     */
    protected $field_factory;

    /**
     * @var Refinery|null
     */
    protected $refinery;

    /**
     * @var Data\Factory|null
     */
    protected $data_factory;

    /**
     * @var \ilSetupPasswordManager|null
     */
    protected $pwm;

    /**
     * @var \ilPluginRawReader|null
     */
    protected $plugin_raw_reader;

    /**
     * @var ImplementationOfInterfaceFinder|null
     */
    protected $interface_finder;

    /**
     * @var ilSetupLanguage|null
     */
    protected $lng;

    public function __construct(
        FieldFactory $field_factory = null,
        Refinery $refinery = null,
        Data\Factory $data_factory = null,
        \ilSetupPasswordManager $pwm = null,
        \ilPluginRawReader $plugin_raw_reader = null,
        ImplementationOfInterfaceFinder $interface_finder = null,
        ilSetupLanguage $lng = null
    ) {
        $this->field_factory = $field_factory;
        $this->refinery = $refinery;
        $this->data_factory = $data_factory;
        $this->pwm = $pwm;
        $this->plugin_raw_reader = $plugin_raw_reader;
        $this->interface_finder = $interface_finder;
        $this->lng = $lng;
    }

    public function getSystemAgents() : array
    {
        $ignore = $this->interface_finder->getIgnoreList();
        array_unshift($ignore, '.*/plugins/');

        $class_names = $this->interface_finder->getMatchingClassNames($ignore);
        $agents = $this->getAgentsByClassNames($class_names);

        return $agents;
    }

    public function getPluginAgents() : array
    {
        foreach ($this->plugin_raw_reader->getPluginNames() as $plugin_name) {
            $agents[] = $this->getPluginAgent($plugin_name);
        }

        return $agents;
    }

    public function getPluginAgent(string $name = "") : Agent
    {
        $ignore = $this->interface_finder->getIgnoreList();
        array_unshift($ignore, '^/Modules/', '^/Services/');

        $class_names = $this->interface_finder->getMatchingClassNames($ignore);
        $agents = $this->getAgentsByClassNames($class_names, true);

        foreach ($agents as $plugin_agent) {
            if (strpos(get_class($plugin_agent), $name) != 0) {
                return $plugin_agent;
            }
        }

        return $this->getDefaultPluginAgent($name);
    }

    private function getAgentsByClassNames(\Iterator $class_names, bool $is_plugin = false) : array
    {
        $agents = [];
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
            if (strtolower($name) === 'common') {
                $agents[strtolower($name)] = new $cls(
                    $this->refinery,
                    $this->data_factory,
                    $this->pwm
                );
                continue;
            }
            if ($is_plugin) {
                $agents[strtolower($name)] = new $cls(
                    $name,
                    $this->refinery,
                    $this->field_factory,
                    $this->lng
                );
                continue;
            }
            $agents[strtolower($name)] = new $cls(
                $this->refinery,
                $this->field_factory,
                $this->lng
            );
        };

        return $agents;
    }

    private function getDefaultPluginAgent(string $name) : Agent
    {
        return new DefaultPluginAgent($name);
    }

    public function buildAgentCollection($agents)
    {
        return new AgentCollection($this->field_factory, $this->refinery, $agents);
    }
}
