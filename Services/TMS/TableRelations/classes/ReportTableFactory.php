<?php

class ReportTableFactory
{
	public function getSelectableReportTable(
		$parent_gui,
		string $cmd,
		string $table_id
	) : SelectableReportTableGUI {
		$table = new SelectableReportTableGUI($parent_gui, $cmd);
		$table->setId($table_id);
		return $table;
	}
}