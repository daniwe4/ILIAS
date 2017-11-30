<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> */

namespace ILIAS\TMS;

use CaT\Ente;

/**
 * In memory implementation of CourseInfo.
 */
class CourseInfoImpl implements CourseInfo {
	/**
	 * @var	Ente\Entity
	 */
	protected $entity;

	/**
	 * @var	string
	 */
	protected $label;

	/**
	 * @var	string
	 */
	protected $value;

	/**
	 * @var	string
	 */
	protected $description;

	/**
	 * @var int	
	 */
	protected $priority;

	/**
	 * @var	int[]
	 */
	protected $contexts;

	public function __construct(Ente\Entity $entity, string $label, $value, string $description, int $priority, array $contexts) {
		$this->entity = $entity;
		$this->label = $label;
		assert(is_int($value) || is_string($value) || is_array($value));
		$this->value = $value;
		$this->description = $description;
		$this->priority = $priority;
		$this->contexts = $contexts;
	}

	/**
	 * @inheritdocs
	 */
	public function entity() {
		return $this->entity;
	}

	
	/**
	 * @inheritdocs
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritdocs
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @inheritdocs
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @inheritdocs
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 * @inheritdocs
	 */
	public function hasContext($context) {
		return in_array($context, $this->contexts);
	}
}

