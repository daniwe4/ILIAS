<?php

require_once 'Services/Repository/classes/class.ilRepositoryObjectPlugin.php';

class ilEnhancedLPReportPlugin extends ilRepositoryObjectPlugin
{
	const ID = "xlpr";

	protected function init()
	{
		parent::init();
	}

	public function getPluginName()
	{
		return 'EnhancedLPReport';
	}

	protected function uninstallCustom()
	{
		$this->db->dropTable('rep_xlpr_data');
	}
}
