<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

/**
 * Tries to achieve a goal. 
 */
class Runner {
	/**
	 * @var	Environment
	 */
	protected $environment;

	/**
	 * @var ConfigurationLoader
	 */
	protected $configuration_loader;

	/**
	 * @var Goal
	 */
	protected $goal;

	public function __construct(Environment $environment, ConfigurationLoader $configuration_loader, Goal $goal) {
		$this->environment = $environment;
		$this->configuration_loader = $configuration_loader;
		$this->goal = $goal;
	}

	public function run() {
		$type = $this->goal->getType();
		$config = $this->configuration_loader->loadConfigurationFor($type);
		$goal = $this->goal
			->withResourcesFrom($this->environment)
			->withConfiguration($config);
		$preconditions = $this->goal->getPreconditions();
		$goal->achieve($this->environment);
	}

	/**
	 * @return \Traversable<Goal>
	 */
	public function allGoals() : \Traversable {
		// TODO: Factor this out in a single class.
		$stack = [$this->goal];
		$returned = [];
		$reverse_deps = [];

		while(count($stack) > 0) {
			$cur = $this->initGoal(
				array_pop($stack)
			);

			$preconditions = $cur->getPreconditions();

			$hash = $cur->getHash();
			if (count($preconditions) === 0) {
				if (!isset($returned[$hash])) {
					yield $cur;
					$returned[$hash] = true;
				}
			}
			else {
				$stack[] = $cur;

				if (isset($reverse_deps[$hash])) {
					$f = null;
					$f = function($cur, $next) use (&$f, $reverse_deps) {
						if (!isset ($reverse_deps[$next])) {
							return;
						}
						if (in_array($cur, $reverse_deps[$next])) {
							throw new UnachievableException(
								"The goals contain a dependency cycle and won't be reachable."
							);
						}
						foreach ($reverse_deps[$next] as $d) {
							$f($cur, $d);
						}
					};
					$f($hash, $hash);
				}

				foreach (array_reverse($preconditions) as $p) {
					$stack[] = $p;
					if (!isset($reverse_deps[$p->getHash()])) {
						$reverse_deps[$p->getHash()] = [];
					}
					$reverse_deps[$p->getHash()][] = $hash;
				}
			}
		}
	}

	protected function initGoal(Goal $goal) : Goal {
		$type = $this->goal->getType();
		$config = $this->configuration_loader->loadConfigurationFor($type);
		return $goal
			->withConfiguration($config)
			->withResourcesFrom($this->environment);
	}
}
