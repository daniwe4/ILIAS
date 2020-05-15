<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types = 1);

use CaT\Plugins\DataChanges\Config\UDF\DB;

class ilBWVUDFGUI
{
	const CMD_SHOW = "show";
	const CMD_SAVE = "save";

	const KEY_GUTBERATEN_ID = "gutberaten_id";

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilGlobalTemplateInterface
	 */
	protected $tpl;

	/**
	 * @var DB
	 */
	protected $db;

	/**
	 * @var Closure
	 */
	protected $txt;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		DB $db,
		Closure $txt
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->db = $db;
		$this->txt = $txt;
	}

	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case self::CMD_SHOW:
				$this->show();
				break;
			case self::CMD_SAVE:
				$this->save();
				break;
			default:
				throw new Exception("Unknown command: ".$cmd);
		}
	}

	public function show(ilPropertyFormGUI $form = null)
	{
		if (is_null($form)) {
			$form = $this->buildForm();
			$this->setValues($form);
		}

		$this->tpl->setContent($form->getHTML());
	}

	protected function setValues(ilPropertyFormGUI $form)
	{
		$gutberaten_id_udf = "";
		$gutberaten_id = $this->db->getUDFFieldIdForBWVID();
		if (! is_null($gutberaten_id)) {
			$gutberaten_id_udf = $gutberaten_id->getFieldId();
		}

		$values = [
			self::KEY_GUTBERATEN_ID => $gutberaten_id_udf
		];

		$form->setValuesByArray($values);
	}

	protected function buildForm()
	{
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->txt('udf_configuration'));
		$form->setFormAction($this->ctrl->getFormAction($this));

		$udf_bwv_id = new ilNumberInputGUI($this->txt('form_udf_bwv_id'), self::KEY_GUTBERATEN_ID);
		$udf_bwv_id->setMinValue(1);
		$udf_bwv_id->allowDecimals(false);
		$udf_bwv_id->setRequired(true);
		$form->addItem($udf_bwv_id);

		$form->addCommandButton(self::CMD_SAVE, $this->txt('save'));

		return $form;
	}

	public function save()
	{
		$form = $this->buildForm();

		if (! $form->checkInput()) {
			$form->setValuesByPost();
			$this->show($form);
			return;
		}

		$post = $_POST;
		$udf_bwv_id = (int)$post[self::KEY_GUTBERATEN_ID];
		$this->db->saveUDFFieldIdForBWVID($udf_bwv_id);

		ilUtil::sendSuccess($this->txt("bwv_fields_saved"), true);
		$this->ctrl->redirect($this, self::CMD_SHOW);
	}

	protected function txt(string $code) : string
	{
		return call_user_func($this->txt, $code);
	}
}