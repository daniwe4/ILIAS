<?php

namespace CaT\Plugins\OnlineSeminar\VC;

/**
 * Inteface for VC data import.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface DataImport
{
    /**
     * Parse the import xlsx file
     *
     * @param string 	$file_path
     *
     * @return array<int, string[]>
     */
    public function parseFile($file_path);
}
