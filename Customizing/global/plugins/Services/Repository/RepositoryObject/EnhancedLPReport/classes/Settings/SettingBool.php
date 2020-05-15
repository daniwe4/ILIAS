<?php

namespace CaT\Plugins\EnhancedLPReport\Settings;

class SettingBool extends Setting
{

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue()
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultToForm()
	{
		return function ($val) {
			return 1;
		};
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm()
	{
		return function ($val) {
			return $val ? true : false;
		};
	}
}
