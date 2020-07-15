<?php

namespace CaT\Plugins\TalentAssessment\Observations;

class ilObservationsReportGUI
{
	use ilFormHelper;

	public function __construct($parent_obj)
	{
		global $DIC;

		$this->g_tpl = $DIC->ui()->mainTemplate();
		$this->g_ctrl = $DIC->ctrl();
		$this->parent_obj = $parent_obj;
		$this->possible_cmd = $parent_obj->getPossibleCMD();
		$this->txt = $parent_obj->getTXTClosure();
		$this->settings = $parent_obj->getSettings();
	}

	public function show()
	{
		$form = new \ilPropertyFormGUI();
		$form->setFormAction($this->g_ctrl->getFormAction($this->parent_obj));

		$this->addReportFormItem($form, $this->settings->Finished());
		$this->fillForm($form);

		if (!$this->settings->Finished()) {
			$form->addCommandButton($this->possible_cmd["CMD_OBSERVATION_SAVE_REPORT"], $this->txt("save"));
		}

		$this->g_tpl->setContent($form->getHtml());
	}

	protected function fillForm(\ilPropertyFormGUI $form)
	{
		$values = array();

		if (!$this->parent_obj->getActions()->getSettings()->Finished()) {
			$middle = $this->parent_obj->getActions()->requestsMiddle();
			$this->parent_obj->getActions()->updatePotential($middle);
			$this->settings = $this->parent_obj->getActions()->getSettings();
		}
		$values = $this->getReportFormValues($values, $this->settings, $this->parent_obj->getActions()->potentialText());
		$form->setValuesByArray($values);
	}

	/**
	 * @param 	string	$code
	 * @return	string
	 */
	public function txt(string $code)
	{
		$txt = $this->txt;

		return $txt($code);
	}
}
