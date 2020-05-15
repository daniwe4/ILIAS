<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\COTextSearch\Settings\DB;

/**
 * @ilCtrl_isCalledBy ilCOTextSearchPluginGUI: ilPCPluggedGUI
 */
class ilCOTextSearchPluginGUI extends ilPageComponentPluginGUI
{
	const CMD_CANCEL = "cancel";
	const CMD_CREATE = "create";
	const CMD_EDIT = "edit";
	const CMD_INSERT = "insert";
	const CMD_UPDATE = "update";

	const F_TITLE = "f_title";
	const F_TEXT_SEARCH = "f_textsearch";

	const CONTENT_ID = "content_id";

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;


	public function __construct()
	{
		parent::__construct();

		global $DIC;
		$this->ctrl = $DIC["ilCtrl"];
		$this->tpl = $DIC["tpl"];
	}

	/**
	 * @inheritDoc
	 * @throws Exception if command is not known
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch ($next_class) {
			default:
				$cmd = $this->ctrl->getCmd();

				switch ($cmd) {
					case self::CMD_CANCEL:
						$this->cancel();
						break;
					case self::CMD_CREATE:
						$this->create();
						break;
					case self::CMD_EDIT:
						$this->edit();
						break;
					case self::CMD_INSERT:
						$this->insert();
						break;
					case self::CMD_UPDATE:
						$this->update();
						break;
					default:
						throw new Exception("Unknown command: ".$cmd);
				}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function insert()
	{
		$form = $this->getCreationForm();
		$this->showForm($form);
	}

	/**
	 * @inheritDoc
	 */
	public function create()
	{
		$form = $this->getCreationForm();

		if (!$form->checkInput()) {
			$form->setValuesByPost();
			$this->showForm($form);
			return;
		}

		$settings = $this->getSettingsDB()->create(
			(int)$this->getPlugin()->getParentId()
		);

		$properties = [
			self::CONTENT_ID => $settings->getId()
		];
		$this->createElement($properties);

		$this->returnToParent();
	}

	/**
	 * @inheritDoc
	 */
	public function edit()
	{
		$form = $this->getEditorForm();
		$this->showForm($form);
	}

	/**
	 * @inheritDoc
	 */
	public function update()
	{
		$this->returnToParent();
	}

	/**
	 * @inheritDoc
	 */
	public function getElementHTML($a_mode, array $a_properties, $plugin_version)
	{
		$this->tpl->addJavaScript($this->getPlugin()->getDirectory()."/templates/js/searchform.js");

		$parent_ref_id = $this->getParentRefId();
		$link = ilLink::_getStaticLink($parent_ref_id, "", true, "&cmd=filter");

		$tpl = new ilTemplate("tpl.searchinput.html", true, true, $this->getPlugin()->getDirectory());
		$tpl->setCurrentBlock("search");
		$tpl->setVariable("POST_VAR", self::F_TEXT_SEARCH);
		$tpl->setVariable("TARGET", $link);
		$tpl->setVariable("BTN_LABEL", $this->txt("search"));
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}

	/**
	 *
	 */
	protected function cancel()
	{
		$this->returnToParent();
	}

	protected function getEditorForm() : ilPropertyFormGUI
	{
		$form = $this->getForm();
		$form->addCommandButton(self::CMD_UPDATE, $this->txt("save"));
		$form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
		return $form;
	}

	protected function getCreationForm() : ilPropertyFormGUI
	{
		$form = $this->getForm();
		$form->addCommandButton(self::CMD_CREATE, $this->txt("save"));
		$form->addCommandButton(self::CMD_CANCEL, $this->txt("cancel"));
		return $form;
	}

	protected function getForm() : ilPropertyFormGUI
	{
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));

		$ti = new ilNonEditableValueGUI("", "", true);
		$ti->setValue($this->txt("no_settings"));
		$form->addItem($ti);

		return $form;
	}

	protected function showForm(ilPropertyFormGUI $form)
	{
		$this->tpl->setContent($form->getHTML());
	}

	protected function txt(string $code) : string
	{
		return $this->getPlugin()->txt($code);
	}

	protected function getSettingsDB() : DB
	{
		return $this->getPlugin()->getSettingsDB();
	}

	protected function getParentRefId() : int
	{
		$ref_ids = ilObject::_getAllReferences(
			$this->getPlugin()->getParentId()
		);

		return (int)array_shift($ref_ids);
	}
}
