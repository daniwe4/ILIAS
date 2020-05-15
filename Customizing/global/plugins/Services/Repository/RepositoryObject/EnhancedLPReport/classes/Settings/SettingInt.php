<?php

namespace CaT\Plugins\EnhancedLPReport\Settings;

class SettingInt extends Setting
{

	/**
	 * @inheritdoc
	 */
	protected function defaultDefaultValue()
	{
		return 0;
	}

	/**
	 * @inheritdoc
	 */
	protected function defaultFromForm()
	{
		return function ($val) {
			return (int)$val;
		};
	}
}
