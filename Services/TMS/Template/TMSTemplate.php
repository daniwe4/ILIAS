<?php

/**
 * Class TMSTemplate
 *
 * Small wrapper for ilTemplate to prevent or change some function calls
 */
class TMSTemplate
{
	/**
	 * @var ilGlobalTemplateInterface
	 */
	protected $tpl;

	public function __construct(ilGlobalTemplateInterface $tpl)
	{
		$this->tpl = $tpl;
	}

	public function setTitle(string $title)
	{
		$this->tpl->setTitle($title);
	}

	public function setContent(string $content)
	{
		$this->tpl->setContent($content);
	}

	public function show() {
		return;
	}
}