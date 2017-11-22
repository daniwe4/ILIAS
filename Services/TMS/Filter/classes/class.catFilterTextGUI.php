<?php
require_once("Services/Form/classes/class.ilTextInputGUI.php");
require_once 'Services/TMS/Filter/classes/class.catFilterGUI.php';

class catFilterTextGUI extends catFilterGUI
{
	protected $filter;
	protected $path;
	protected $val;
	protected $query_link;

	public function __construct($filter, $path)
	{
		$this->filter = $filter;
		$this->path = $path;
		$this->query_link = $filter->queryLink();
	}

	/**
	 * @inheritdoc
	 */
	public function formElement()
	{
		$input = new ilTextInputGUI($this->filter->label(), "filter[$this->path]");
		$input->setInfo($this->filter->description());
		if($this->query_link) {
			$input->setDataSource($this->query_link);
			$input->setSubmitFormOnEnter(true);
		}
		if ($this->val) {
			$input->setValue($this->val);
		}

		return $input;
	}

	public function setValue($val)
	{
		$this->val = $val;
	}
}
