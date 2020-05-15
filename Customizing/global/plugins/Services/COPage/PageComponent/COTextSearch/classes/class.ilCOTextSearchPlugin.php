<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\COTextSearch\Settings;

class ilCOTextSearchPlugin extends ilPageComponentPlugin
{
	const PLUGIN_ID = "cots";
	const PLUGIN_NAME = "COTextSearch";
	const CONTENT_ID = "content_id";

	/**
	 * @var Settings\DB
	 */
	protected $db;

	protected static $allowed_types = [
		"xtrs"
	];

	/**
	 * @var self | null
	 */
	protected static $instance = NULL;

	/**
	 * @return ilCOTextSearchPlugin
	 */
	public static function getInstance() {
		if (self::$instance === NULL) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getPluginName() {
		return self::PLUGIN_NAME;
	}

	/**
	 * @inheritDoc
	 */
	public function isValidParentType($a_type) {
		return in_array($a_type, self::$allowed_types);
	}

	/**
	 * @inheritDoc
	 */
	public function onDelete($properties, $plugin_version)
	{
		$this->getSettingsDB()->deleteFor((int)$properties[self::CONTENT_ID]);
	}

	/**
	 * @inheritDoc
	 */
	public function onClone(&$properties, $plugin_version)
	{

	}

	public function getSettingsDB() : Settings\DB
	{
		if(is_null($this->db)) {
			global $DIC;
			$this->db = new Settings\ilDB($DIC["ilDB"]);
		}

		return $this->db;
	}
}