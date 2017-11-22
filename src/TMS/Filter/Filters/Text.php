<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Filters;

class Text extends Filter {

	protected $query_link;

	public function __construct(
		\ILIAS\TMS\Filter\FilterFactory $factory,
		string $label,
		string $description,
		bool $visible,
		array $mappings = array(),
		array $mapping_result_types = array()
	) {
		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);
		$this->setVisible($visible);
	}

	public function withQueryLink($query_link)
	{
		$other = clone $this;
		$other->query_link = $query_link;
		return $other;
	}

	public function queryLink()
	{
		return $this->query_link;
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		return $this->factory->type_factory()->string();
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
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Text(
			$this->factory,
			$this->label(),
			$this->description(),
			$this->isVisible(),
			$mappings,
			$mapping_result_types
		);
	}
}
