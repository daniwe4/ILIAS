<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DataChanges\Config\Log\ilDB;
use CaT\Plugins\DataChanges\Config\Log\LogEntry;

class ilDCLogGUI extends TMSTableParentGUI
{
	const CMD_SHOW_LOG = 'showLogConfig';

	const TABLE_ID = 'log';

	const COLUMN_ACTION = "action";
	const COLUMN_TARGET = "target_id";
	const COLUMN_EDITOR = "editor_id";
	const COLUMN_CHANGE = "change_date_time";
	const COLUMN_REASON = "reason";

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilGlobalTemplateInterface
	 */
	protected $tpl;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var Closure
	 */
	protected $txt;

	/**
	 * @var string
	 */
	protected $plugin_path;

	public function __construct(
		ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
		ilDB $db,
		Closure $txt,
		string $plugin_path
	) {
		$this->ctrl = $ctrl;
		$this->tpl = $tpl;
		$this->db = $db;
		$this->txt = $txt;
		$this->plugin_path = $plugin_path;
	}

	/**
	 * @throws Exception
	 */
	public function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		switch ($cmd) {
			case self::CMD_SHOW_LOG:
				$this->showLog();
				break;
			default:
				throw new Exception('Unknown command: '.$cmd);
		}
	}

	protected function showLog()
	{
		$table = $this->getTMSTableGUI();

		$order_field = $table->getOrderField();
		$direction = $table->getOrderDirection();

		$log_entries = $this->db->selectAll($order_field, $direction);

		$table->setData($log_entries);

		$this->tpl->setContent($table->getHTML());
	}

	protected function getTMSTableGUI() : ilTMSTableGUI
	{
		$table = parent::getTMSTableGUI();

		$table->setTitle($this->txt('log'));
		$table->setTopCommands(false);
		$table->setRowTemplate('tpl.table_dc_log_row.html', $this->plugin_path);
		$table->setFormAction($this->ctrl->getFormAction($this));
		$table->setDefaultOrderField(self::COLUMN_CHANGE);
		$table->determineOffsetAndOrder();
		$table->setExternalSegmentation(false);
		$table->setDefaultOrderDirection("desc");

		$table->addColumn($this->txt('log_id'));
		$table->addColumn($this->txt('action'), self::COLUMN_ACTION);
		$table->addColumn($this->txt('target'), self::COLUMN_TARGET);
		$table->addColumn($this->txt('editor'), self::COLUMN_EDITOR);
		$table->addColumn($this->txt('change_date_time'), self::COLUMN_CHANGE);
		$table->addColumn($this->txt('reason'), self::COLUMN_REASON);

		return $table;
	}

	/**
	 * Get the closure table should be filled with
	 */
	protected function fillRow() : Closure
	{
		return function(ilTMSTableGUI $table, LogEntry $log_entry) {
			$tpl = $table->getTemplate();
			$change_date_time = $log_entry->getChangeDateTime()->format('d.m.Y H:i:s');

			$tpl->setVariable('LOG_ID', $log_entry->getLogId());
			$tpl->setVariable('ACTION', $log_entry->getAction());
			$tpl->setVariable('TARGET', $log_entry->getTargetId());
			$tpl->setVariable('EDITOR', $log_entry->getEditorId());
			$tpl->setVariable('CHANGE_DATE_TIME', $change_date_time);
			$tpl->setVariable('REASON', htmlspecialchars_decode($log_entry->getReason()));
		};
	}

	protected function tableCommand() : string
	{
		return self::CMD_SHOW_LOG;
	}

	protected function tableId() : string
	{
		return self::TABLE_ID;
	}

	protected function txt(string $code): string
	{
		return call_user_func($this->txt, $code);
	}
}
