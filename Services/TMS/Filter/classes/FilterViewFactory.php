<?php

declare(strict_types=1);

class FilterViewFactory
{
	public function getFlatviewGUI($parent_obj, $sequence, $display_filter, $cmd_save) : catFilterFlatViewGUI
	{
		return new catFilterFlatViewGUI($parent_obj, $sequence, $display_filter, $cmd_save);
	}
}