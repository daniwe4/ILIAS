<?php
require_once 'Services/Tracking/classes/class.ilLPTableBaseGUI.php';
include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';

use CaT\Plugins\EnhancedLPReport\Excel as Excel;

class ilEnhancedLPTableGUI extends ilLPTableBaseGUI
{

	const EXPORT_XLSX = 3;

	protected $column_widths = array(
			'gender' => "100px"
			,'institution' => "300px"
			,'email' => "500px"
			,'region' => "300px"
			,'last_login' => "500px"
			,'active' => "100px"
			,'member'=> "100px"
			,'status' => "100px"
		);

	public function __construct($a_report_obj, $a_parent_obj, $a_parent_cmd = "")
	{
		$this->setId("elpt_".$a_report_obj->getId());

		global $ilCtrl, $lng, $ilUser;
		$this->p_lng = $a_report_obj->plugin();
		$this->g_lng = $lng;
		$this->g_usr = $ilUser;
		$this->report_obj = $a_report_obj;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->initFilter();
		$this->determineSelectedFilters();
		$this->anonymized = false;
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setEnableTitle(true);
		$this->setShowTemplates(true);
		$this->setExportFormats(array());
		$this->configureColumns();
		$this->getItems($a_report_obj);
		$this->selectable_cols = $this->getSelectedColumns();
		$this->setRowTemplate("tpl.enhanced_lp_report_row.html", $a_report_obj->plugin()->getDirectory());
		$this->setExportFormats(array(self::EXPORT_XLSX));
		$this->setDefaultOrderField('login');
		$this->styles = array("table" => "tableReport");
	}

	public function storeProperty($type, $value)
	{
		if ($type !== 'order') {
			parent::storeProperty($type, $value);
		}
	}

	public function loadProperty($type)
	{
		if ($type !== 'order') {
			return parent::loadProperty($type);
		}
		return;
	}

	protected function configureColumns()
	{
		$this->addColumn($this->p_lng->txt('login'), 'login');
		$this->addColumn($this->p_lng->txt('firstname'), 'firstname');
		$this->addColumn($this->p_lng->txt('lastname'), 'lastname');
		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c) {
			$this->addColumn($labels[$c]["txt"], $c, $this->column_widths[$c]);
		}
	}

	/**
	 * Add xlsx-export functionality
	 */
	public function setExportFormats(array $formats)
	{
		parent::setExportFormats($formats);
		if (!is_array($this->export_formats)) {
			$this->export_formats = array();
		}
		if (in_array(self::EXPORT_XLSX, $formats)) {
			$this->export_formats[self::EXPORT_XLSX] = 'rep_robj_xlpr_tbl_xlsx_export';
		}
	}

	public function exportData($format, $send = false)
	{
		if ($this->dataExists()) {
			switch ($format) {
				case self::EXPORT_XLSX:
					$this->exportXlsx();
					if ($send) {
						exit();
					}
					break;
				default:
					parent::exportData($format, $send);
			}
		}
	}

	protected function exportXlsx()
	{
		if (!$this->getExternalSorting() && $this->enabled["sort"]) {
			$this->determineOffsetAndOrder(true);
			$this->row_data = ilUtil::sortArray(
				$this->row_data,
				$this->getOrderField(),
				$this->getOrderDirection(),
				$this->numericOrdering($this->getOrderField())
			);
		}
		$workbook = new Excel\spoutXLSXWriter();
		$sheet_name = "report";
		$workbook
			->addSheet($sheet_name)
			->setRowFormatWrap()
			->writeRow(array($this->p_lng->txt('report_date_header_export'),
				(new DateTime)->format('d.m.Y H:i')))
			->writeRow(array($this->p_lng->txt('report_creator_header_export'),
				$this->g_usr->getFirstname().' '.$this->g_usr->getLastname()))
			->writeRow(array(''));

		$workbook
			->setRowFormatBold();

		$header = array();
		foreach ($this->column as $col) {
			$header[] = $col["text"];
		}

		$workbook
			->writeRow($header)
			->setRowFormatWrap();

		foreach ($this->row_data as $entry) {
			$row = array();
			foreach ($this->activeColumns() as $col) {
				if ($col === 'status') {
					switch ($entry[$col]) {
						case ilLPStatus::LP_STATUS_COMPLETED_NUM:
							$row[$col] = $this->g_lng->txt(ilLPStatus::LP_STATUS_COMPLETED);
							break;
						case ilLPStatus::LP_STATUS_FAILED_NUM:
							$row[$col] = $this->g_lng->txt(ilLPStatus::LP_STATUS_FAILED);
							break;
						case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
							$row[$col] = $this->g_lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
							break;
						case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
							$row[$col] = $this->g_lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
							break;
					}
				} elseif ($col === 'last_login') {
					$row[$col] = (new DateTime($entry[$col]))->format('d.m.Y H:i');
				} elseif ($col === 'active' || $col === 'member') {
					$row[$col] = $this->p_lng->txt(self::NO);
					if ($entry[$col]) {
						$row[$col] = $this->p_lng->txt(self::YES);
					}
				} else {
					$row[$col] = $entry[$col];
				}
			}
			$workbook->writeRow($row);
		}
		$workbook->offerDownload("report.xlsx");
	}

	protected function activeColumns()
	{
		return array_merge(array('login' => 'login','firstname' => 'firstname','lastname' => 'lastname'), $this->getSelectedColumns());
	}

	const NONE = 'none';
	const YES = 'yes';
	const NO = 'no';
	const LP_ALL = 'all';

	public function initFilter()
	{
		$this->setDisableFilterHiding(true);
		$column = 'login';
		$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_TEXT, true, $this->p_lng->txt($column));
		$this->filter[$column] = $item->getValue();
		$column = 'firstname';
		$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_TEXT, true, $this->p_lng->txt($column));
		$this->filter[$column] = $item->getValue();
		$column = 'lastname';
		$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_TEXT, true, $this->p_lng->txt($column));
		$this->filter[$column] = $item->getValue();
		foreach ($this->getSelectableColumns() as $column => $meta) {
			switch ($column) {
				case "email":
				case "institution":
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_TEXT, true, $meta["txt"]);
					$this->filter[$column] = $item->getValue();
					break;
				case "status":
					include_once "Services/Tracking/classes/class.ilLPStatus.php";
					$item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT, true, $meta["txt"]);
					$item->setOptions(array(self::LP_ALL => $this->g_lng->txt("trac_all"),
						ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM+1 => $this->g_lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED),
						ilLPStatus::LP_STATUS_IN_PROGRESS_NUM+1 => $this->g_lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS),
						ilLPStatus::LP_STATUS_COMPLETED_NUM+1 => $this->g_lng->txt(ilLPStatus::LP_STATUS_COMPLETED),
						ilLPStatus::LP_STATUS_FAILED_NUM+1 => $this->g_lng->txt(ilLPStatus::LP_STATUS_FAILED)));
					$this->filter["status"] = $item->getValue();
					if ($this->filter["status"]) {
						$this->filter["status"]--;
					}
					break;
				case "active":
				case "member":
					include_once "Services/Tracking/classes/class.ilLPStatus.php";
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_SELECT, true, $meta["txt"]);
					$item->setOptions(array(self::YES => $this->p_lng->txt('yes'), self::NO => $this->p_lng->txt('no'),self::NONE => '--'));
					$val = $item->getValue();
					if ($val === false) {
						$val = self::YES;
					}
					$this->filter[$column] = $val;
					break;
			}
		}
	}

	protected function fillRow($data)
	{
		foreach ($this->activeColumns() as $column) {
			$data_v = $data[$column];
			if ($data_v === null) {
				$data_v = '-';
			}
			switch ($column) {
				case 'login':
				case 'firstname':
				case 'lastname':
					$this->tpl->setVariable('VAL_'.strtoupper($column), $data_v);
					break;
				case 'email':
				case 'institution':
				case 'gender':
					$this->tpl->setCurrentBlock($column);
					$this->tpl->setVariable('VAL_'.strtoupper($column), $data_v);
					$this->tpl->parseCurrentBlock();
					break;
				case 'member':
				case 'active':
					$this->tpl->setCurrentBlock($column);
					if ($data_v) {
						$aux = $this->p_lng->txt('yes');
					} else {
						$aux = $this->p_lng->txt('no');
					}
					$this->tpl->setVariable('VAL_'.strtoupper($column), $aux);
					$this->tpl->parseCurrentBlock();
					break;
				case 'last_login':
					$this->tpl->setCurrentBlock($column);
					$val = (new DateTime($data[$column]))->format('d.m.Y H:i');
					$this->tpl->setVariable('VAL_'.strtoupper($column), $val);
					$this->tpl->parseCurrentBlock();
					break;
				case 'status':
					$path = ilLearningProgressBaseGUI::_getImagePathForStatus($data_v);
					$text = ilLearningProgressBaseGUI::_getStatusText($data_v);
					$this->tpl->setCurrentBlock($column);
					$this->tpl->setVariable('VAL_'.strtoupper($column), ilUtil::img($path, $text));
					$this->tpl->parseCurrentBlock();
					break;
				default:
					if (preg_match('#^udf_#', $column) === 1) {
						$this->tpl->setCurrentBlock('udf');
						$this->tpl->setVariable('VAL_UDF', $data_v);
						$this->tpl->parseCurrentBlock();
					}
			}
		}
	}

	protected function getFilterSettings()
	{
		return $this->filter;
	}

	protected function getItems($a_report_obj)
	{

		$this->setData($a_report_obj->getData($this->getFilterSettings(), $this->activeColumns()));
	}

	public function getSelectableColumns()
	{
		$return = array();
		$s_udf = $this->report_obj->getSelectableUDF();
		$return['gender'] = array('txt' => $this->p_lng->txt('gender'), 'default' => false);
		$return['institution'] = array('txt' => $this->p_lng->txt('institution'), 'default' => false);
		$return['email'] = array('txt' => $this->p_lng->txt('email'), 'default' => true);
		foreach ($s_udf as $ud_id => $data) {
			$return[$ud_id] = array('txt' => $data['field_name'], 'default' => false);
		}
		$return['last_login'] = array('txt' => $this->p_lng->txt('last_login'), 'default' => false);
		$return['active'] = array('txt' => $this->p_lng->txt('active'), 'default' => true);
		$return['member'] = array('txt' => $this->p_lng->txt('member'), 'default' => true);
		$return['status'] = array('txt' => $this->p_lng->txt('status'), 'default' => true);
		return $return;
	}
}
