<?php

namespace CaT\Plugins\EnhancedLPReport\Settings;

class SettingFactory
{
	protected $db;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function settingInt($id, $name)
	{
		return new SettingInt($id, $name);
	}

	public function settingString($id, $name)
	{
		return new SettingString($id, $name);
	}

	public function settingFloat($id, $name)
	{
		return new SettingFloat($id, $name);
	}

	public function settingBool($id, $name)
	{
		return new SettingBool($id, $name);
	}

	public function settingRichText($id, $name)
	{
		return new SettingRichText($id, $name);
	}

	public function settingText($id, $name)
	{
		return new SettingText($id, $name);
	}

	public function settingListInt($id, $name)
	{
		return new SettingListInt($id, $name);
	}

	public function settingHiddenInt($id, $name)
	{
		return new SettingHiddenInt($id, $name);
	}

	public function settingHiddenString($id, $name)
	{
		return new SettingHiddenString($id, $name);
	}

	public function reportSettings($table)
	{
		return new ReportSettings($table, $this->db);
	}

	public function reportSettingsDataHandler()
	{
		return new ReportSettingsDataHandler($this->db);
	}

	public function reportSettingsFormHandler()
	{
		return new ReportSettingsFormHandler();
	}
}
