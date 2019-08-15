<?php

use CaT\Libs\ExcelWrapper\Spout\SpoutWriter;

class ExportFactory
{
	public function getSpoutWriter() : SpoutWriter
	{
		return new SpoutWriter();
	}
}