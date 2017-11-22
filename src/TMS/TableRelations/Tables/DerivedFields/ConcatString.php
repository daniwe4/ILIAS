<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

class ConcatString extends T\DerivedField  {

	protected $append;
	protected $arg;

	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field, $append = "") {
		$this->derived_from[] = $field;
		$this->arg = $field;
		$this->append = $append;
		parent::__construct($f, $name);
	}


	public function argument() {
		return $this->arg;
	}

	public function append()
	{
		return $this->append;
	}

}
