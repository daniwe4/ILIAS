<?php

class catMethodVariableFormGUI extends ilPropertyFormGUI {
	/**
	 * @var string
	 */
	protected $method;

	public function __construct()
	{
		parent::__construct();
		$this->method = "post";
	}

	public function setMethod(string $method)
	{
		$this->method = $method;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	/**
	* @inheritdoc
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.form.html", true, true, "Services/Form");
		
		// this line also sets multipart, so it must be before the multipart check
		$content = $this->getContent();
		if ($this->getOpenTag())
		{
			$opentpl = new ilTemplate('tpl.form_open.html', true, true, "Services/Form");
			$opentpl->setVariable("METHOD", $this->getMethod());
			if ($this->getTarget() != "")
			{
				$opentpl->setCurrentBlock("form_target");
				$opentpl->setVariable("FORM_TARGET", $this->getTarget());
				$opentpl->parseCurrentBlock();
			}
			if ($this->getName() != "")
			{
				$opentpl->setCurrentBlock("form_name");
				$opentpl->setVariable("FORM_NAME", $this->getName());
				$opentpl->parseCurrentBlock();
			}
			if ($this->getPreventDoubleSubmission())
			{
				$opentpl->setVariable("FORM_CLASS", "preventDoubleSubmission");
			}

			if ($this->getMultipart())
			{
				$opentpl->touchBlock("multipart");

			}
			$opentpl->setVariable("FORM_ACTION", $this->getFormAction());
			if ($this->getId() != "")
			{
				$opentpl->setVariable("FORM_ID", $this->getId());
			}
			$opentpl->parseCurrentBlock();
			$tpl->setVariable('FORM_OPEN_TAG', $opentpl->get());
		}
		$tpl->setVariable("FORM_CONTENT", $content);
		if (!$this->getKeepOpen())
		{
			$tpl->setVariable("FORM_CLOSE_TAG", "</form>");
		}

		$html = $tpl->get();
		
		// #13531 - get content that has to reside outside of the parent form tag, e.g. panels/layers
		if(is_array($this->getItems())) {
			foreach($this->getItems() as $item)
			{
				// #13536 - ilFormSectionHeaderGUI does NOT extend ilFormPropertyGUI ?!
				if(method_exists($item, "getContentOutsideFormTag"))
				{
					$outside = $item->getContentOutsideFormTag();
					if($outside)
					{
						$html .= $outside;
					}
				}
			}
		}

		return $html;
	}
}
