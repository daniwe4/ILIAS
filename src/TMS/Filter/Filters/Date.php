<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Filters;

class Date extends Filter {
	/**
	 * @var	\DateTime
	 */
	private $default;

	public function __construct(
		\ILIAS\TMS\Filter\FilterFactory $factory,
		string $label,
		string $description,
		bool $visible,
		\DateTime $default = null,
		array $mappings = array(),
		array $mapping_result_types = array()
	) {
		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);
		$this->setVisible($visible);

		if ($default === null) {
			$this->default = new \DateTime(date("Y")."-01-01");
		}
		else {
			$this->default = $default;
		}

	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		$tf = $this->factory->type_factory();
		return $tf->either($tf->cls("\\DateTime"), $tf->string());
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->original_content_type();
	}

	/**
	 * @inheritdocs
	 */
	protected function raw_content($input) {
		return $input;
	}

	/**
	 * Set the default for the beginning of this filter.
	 *
	 * @param	\DateTime
	 * @return	Date
	 */
	public function default_date(\DateTime $dt = null) {
		if ($dt === null) {
			return $this->default;
		}

		list($ms, $mrts) = $this->getMappings();
		return new Date(
				$this->factory,
				$this->label(),
				$this->description(),
				$this->isVisible(),
				$dt,
				$ms,
				$mrts);
	}


	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Date(
			$this->factory,
			$this->label(),
			$this->description(),
			$this->isVisible(),
			$this->default,
			$mappings,
			$mapping_result_types
		);

	}
}
