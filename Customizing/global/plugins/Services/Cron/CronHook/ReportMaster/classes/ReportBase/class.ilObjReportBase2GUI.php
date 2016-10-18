<?php

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/CaTUIComponents/classes/class.catTitleGUI.php';
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/interfaces/interface.ExcelWriter.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.reportSettingsFormHandler.php");
require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportSettings/class.settingFactory.php");

abstract class ilObjReportBase2GUI extends ilObjectPluginGUI {

	protected function afterConstructor() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog, $ilAccess, $ilTabs;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;
		$this->gLog = $ilLog;
		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->s_f = new settingFactory($this->gIldb);
		$this->settings_form_handler = $this->s_f->reportSettingsFormHandler();
		// TODO: this is crapy. The root cause of this problem is, that the
		// filter should no need to know about it's action. The _rendering_
		// of the filter needs to know about the action.

		$this->title = null;
	}


	public function setTabs() {
		if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {

			// tab for the "show content" command
			if ($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
				$this->gTabs->addTab("content", $this->object->plugin->txt($this->getType()."_content"),
				$this->gCtrl->getLinkTarget($this, "showContent"));
			}

			// standard info screen tab
			$this->addInfoTab();

			// a "properties" tab
			$this->gTabs->addTab("properties", $this->object->plugin->txt($this->getType()."_properties"),
			$this->gCtrl->getLinkTarget($this, "settings"));
			// standard epermission tab
			$this->addPermissionTab();
		}
	}

	/**
	* Besides usual report commands (exportXLS, view, ...) showMenu goes here
	*/
	public function performCommand() {
		$cmd = $this->gCtrl->getCmd("showContent");
		$this->cmd = $cmd;
		switch ($cmd) {
			case "saveSettings":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("properties");
					return $this->saveSettings();
				}
				break;
			case "settings":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->addSubTabTarget("edit_settings",
												 $this->gCtrl->getLinkTarget($this,'settings'),
												 "write", get_class($this));
					$this->gTabs->addSubTabTarget("report_query_view",
												 $this->gCtrl->getLinkTarget($this,'query_view'),
												 "write", get_class($this));
					$this->gTabs->activateTab("properties");
					$this->gTabs->activateSubTab("edit_settings");
					return $this->renderSettings();
				}
				break;
			case "query_view":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->addSubTabTarget("edit_settings",
												 $this->gCtrl->getLinkTarget($this,'settings'),
												 "write", get_class($this));
					$this->gTabs->addSubTabTarget("report_query_view",
												 $this->gCtrl->getLinkTarget($this,'settings'),
												 "write", get_class($this));
					$this->gTabs->activateTab("properties");
					$this->gTabs->activateSubTab("report_query_view");
					return $this->renderQueryView();
				}
				break;
			case "exportexcel":
				if($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
					$this->exportExcel();
				}
				exit();
			case "showContent":
				if($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					return $this->renderReport();
				}
				break;
		}
	}

	public function performCustomCommand($cmd) {
		return false;
	}

	public function getAfterCreationCmd() {
		return "settings";
	}

	public function getStandardCmd() {
		return "showContent";
	}

	/**
	 * render query for debugging purposes
	 * a filter is present and may be modified to observe the effects on query
	 */
	public function renderQueryView() {
		include_once "Services/Form/classes/class.ilNonEditableValueGUI.php";
		$this->prepareReport();

		$content = $this->filter !== null ? $this->filter->render() : "";
		$form = new ilNonEditableValueGUI($this->gLng->txt("report_query_text"));
		$form->setValue($this->object->buildQueryStatement());
		$settings_form = new ilPropertyFormGUI();
		$settings_form->addItem($form);
		$content .= $settings_form->getHTML();
		$this->gTpl->setContent($content);
	}


	protected function prepareReport() {
		$this->addGetParametersToReport();
		$this->object->initSpace();
		$this->filter = $this->prepareFilter($this->object);
		$this->enableRelevantParametersCtrl();
		$table = new catSelectableReportTableGUI($this,'showContent');
		$this->disableRelevantParametersCtrl();
		$this->table = $this->object->prepareTable($table);
	}

	protected function addGetParametersToReport() {

	}

	protected function prepareFilter($object) {
		$filter = $object->filter();
		$this->filter_settings = $this->loadFilterSettings();

		$display = new \CaT\Filter\DisplayFilter
						( new \CaT\Filter\FilterGUIFactory
						, new \CaT\Filter\TypeFactory
						);
		$this->ecoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
		$object->addRelevantParameter('filter_params',$this->ecoded_filter_settigs);

		if(count($this->filter_settings) > 0) {
			$object->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));
		}
		require_once("Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilterFlatViewGUI.php");
		$filter_view = new catFilterFlatViewGUI($this, $filter, $display, $this->cmd);
		return $filter_view;
	}


	protected function loadFilterSettings() {
		if(isset($_POST['filter'])) {
			return $_POST['filter'];
		} elseif(isset($_GET['filter_params'])) {
			return $this->decodeFilterParams($_GET['filter_params']);
		}
		return array();
	}

	protected function encodeFilterParams(array $filter_params) {
		return base64_encode(json_encode($filter_params));
	}

	protected function decodeFilterParams($encoded_filter) {
		return json_decode(base64_decode($encoded_filter),true);
	}

	/**
	 * render report.
	 */
	public function renderReport() {
		$this->prepareReport();
		$this->title = $this->prepareTitle(catTitleGUI::create());
		$this->spacer = $this->prepareSpacer(new catHSpacerGUI());
		$this->gTpl->setContent($this->render());
	}

	protected function render() {
		$this->gTpl->setTitle(null);

		return 	($this->title !== null ? $this->title->render() : "")
				. $this->renderFilter()
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderTable();
	}

	protected function renderTable() {
		$data = $this->object->deliverData(array($this,'transformResultRowTable'));
		$content = $this->renderUngroupedTable($data);
		//export-button
		$export_btn = "";
		if (count($data) > 0) {
			$export_btn = $this->renderExportButton();
		}

		return	 $export_btn
				.$content
				.$export_btn;
	}

	protected function renderFilter() {
		$this->enableRelevantParametersCtrl();
		$filter_rendered = $this->filter->render($this->filter_settings);
		$this->disableRelevantParametersCtrl();
		return $filter_rendered;
	}

	protected function renderExportButton() {
		$this->enableRelevantParametersCtrl();
		$export_btn = '<a class="submit exportXlsBtn"'
						. 'href="'
						.$this->gCtrl->getLinkTarget($this, "exportexcel")
						.'">'
						.$this->gLng->txt("gev_report_excel_export")
						.'</a>';
		$this->disableRelevantParametersCtrl();
		return $export_btn;
	}

	protected function prepareTitle($a_title) {
		$a_title->title($this->object->getTitle())
				->subTitle($this->object->getDescription())
				->setVideoLink($this->object->settings['video_link'])
				->setVideoLinkText($this->object->master_plugin->txt("rep_video_desc"))
				->setPdfLink($this->object->settings['pdf_link'])
				->setPdfLinkText($this->object->master_plugin->txt("rep_pdf_desc"))
				->setToolTipText($this->object->settings['tooltip_info'])
				->useLng(false);
		$a_title->image("GEV_img/ico-head-rep-billing.png");
		return $a_title;
	}

	protected function prepareSpacer($a_spacer) {
		return $a_spacer;
	}

	protected function renderUngroupedTable($data) {
		$this->table->setRowTemplate('tpl.report_exam_biografy_row.html',$this->object->plugin->getDirectory());

		$cnt = count($data);
		$this->table->setLimit($cnt);
		$this->table->setMaxCount($cnt);

		$this->table->setData($data);
		$this->enableRelevantParametersCtrl();
		$return = $this->table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}


	protected function getExcelWriter() {
		require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.spoutXLSXWriter.php';
		$workbook = new spoutXLSXWriter();
		return $workbook;
	}

	/**
	 * provide xlsx version of report for download.
	 */
	protected function exportExcel() {
		$this->prepareReport();
		$relevant_columns = $this->table->relevantColumns();
		$workbook = $this->getExcelWriter();

		$sheet_name = "report";
		$workbook
			->addSheet($sheet_name)
			->setRowFormatBold();

		$header = array();
		foreach ($relevant_columns as $col) {
			if ($col['no_excel']) {
				continue;
			}
			$header[] = $col['txt'];
		}

		$workbook
			->writeRow($header)
			->setRowFormatWrap();

		foreach ($this->object->deliverData(array($this,'transformResultRowExcel')) as $entry) {
			$row = array();
			foreach ($relevant_columns as $column_id => $col) {
				if ($col['no_excel']) {
					continue;
				}
				$row[$column_id] = $entry[$column_id];
			}
			$workbook->writeRow($row);
		}

		$workbook->offerDownload("report.xlsx");
	}

	public function transformResultRowTable($rec) {
		return $rec;
	}

	public function transformResultRowExcel($rec) {
		return $this->transformResultRowCommon($rec);
	}

	protected function transformResultRowCommon($rec) {
		return $rec;
	}


	final protected static function replaceEmpty($a_rec) {
		global $lng;
		foreach ($a_rec as $key => $value) {
			if ($a_rec[$key] == "-empty-" || $a_rec[$key] == "0000-00-00" || $a_rec[$key] === null) {
				$a_rec[$key] = $lng->txt("gev_table_no_entry");
			}
		}
		return $a_rec;
	}

	/**
	* housekeeping the get parameters passed to ctrl
	*/
	final protected function enableRelevantParametersCtrl() {
		foreach ($this->object->getRelevantParameters() as $get_parameter => $get_value) {
			$this->gCtrl->setParameter($this, $get_parameter, $get_value);
		}
	}

	final protected function disableRelevantParametersCtrl() {
		foreach ($this->object->getRelevantParameters() as $get_parameter => $get_value) {
			$this->gCtrl->setParameter($this, $get_parameter, null);
		}
	}

	protected function renderSettings() {
		$settings_form = $this->fillSettingsFormFromDatabase($this->settingsForm());
		$this->gTpl->setContent($settings_form->getHtml());
	}

	protected function fillSettingsFormFromDatabase($settings_form) {
		$data = $this->object->settings;
		$title = $this->object->getTitle();
		$desc = $this->object->getDescription();

		$settings_form->getItemByPostVar('title')->setValue($title);
		$settings_form->getItemByPostVar('description')->setValue($desc);

		$settings_form = $this->settings_form_handler->insertValues($data, $settings_form, $this->object->global_report_settings);
		$settings_form = $this->settings_form_handler->insertValues($data, $settings_form, $this->object->local_report_settings);
		return $settings_form;
	}

	protected function saveSettings() {
		$settings_form = $this->settingsForm();
		$settings_form->setValuesByPost();
		if($settings_form->checkInput()) {
			$this->saveSettingsData($settings_form);
			$red = $this->gCtrl->getLinkTarget($this, "settings", "", false, false);
			ilUtil::redirect($red);
		}
		$this->gTpl->setContent($settings_form->getHtml());
	}

	protected function saveSettingsData($settings_form) {
		$this->object->setTitle($settings_form->getItemByPostVar('title')->getValue());
		$this->object->setDescription($settings_form->getItemByPostVar('description')->getValue());

		$settings = array_merge($this->settings_form_handler->extractValues($settings_form,$this->object->global_report_settings)
								,$this->settings_form_handler->extractValues($settings_form,$this->object->local_report_settings));
		$this->object->setSettingsData($settings);

		$this->object->doUpdate();
		$this->object->update();
	}

	protected function settingsForm() {
		$settings_form = new ilPropertyFormGUI();
		$settings_form->setFormAction($this->gCtrl->getFormAction($this));
		$settings_form->addCommandButton("saveSettings", $this->gLng->txt("save"));

		$title = new ilTextInputGUI($this->gLng->txt('title'),'title');
		$title->setRequired(true);
		$settings_form->addItem($title);

		$description = new ilTextAreaInputGUI($this->gLng->txt('description'),'description');
		$settings_form->addItem($description);

		$this->settings_form_handler->addToForm($settings_form, $this->object->global_report_settings);
		$this->settings_form_handler->addToForm($settings_form, $this->object->local_report_settings);
		return $settings_form;
	}
}