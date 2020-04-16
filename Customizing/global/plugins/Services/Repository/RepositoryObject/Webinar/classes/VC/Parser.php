<?php

declare(strict_types=1);

namespace CaT\Plugins\Webinar\VC;

/**
 * Describes functions a xlsx or csv parser must implement to get content
 * of uploaded file.
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
interface Parser
{
    public function load(string $file);

    /**
     * get the unformatted cell value
     * @return string|int
     */
    public function cellValue(string $column, int $row);

    /**
     * get the cell value according to xls format
     * @return string|int
     */
    public function formatedCellValue(string $column, int $row);

    /**
     * Get the highest row number with any value
     * @return string|int
     */
    public function getHighestRow();
}
