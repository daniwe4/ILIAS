<?php

namespace CaT\Plugins\ParticipationsImport\Data;

interface DataExtractor
{
    /**
     * Get the content of file renaming rows according to field conversions.
     * We assume row titles are in the first row, except when $no_header = true.
     * Then we will assume, that the order of fields corresponds to
     * order in $field_conversions.
     *
     * @param	string	$path_to_file
     * @param	string[string]	$field_conversions
     * @param 	bool	$no_header
     * @return	mixed[][]
     */
    public function extractContent(string $path_to_file, array $field_conversion, bool $no_header = false) : array;
}
