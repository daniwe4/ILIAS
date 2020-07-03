<?php

declare(strict_types=1);

namespace CaT\Plugins\OnlineSeminar\VC;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Implementation of parser interface for xls/xlsx import
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class PHPSpreadsheetParser implements Parser
{
    /**
     * @inheritdoc
     */
    public function load(string $file)
    {
        $this->php_spreadsheet = IOFactory::load($file);
    }

    /**
     * @inheritdoc
     */
    public function cellValue(string $column, int $row)
    {
        return $this->php_spreadsheet->getActiveSheet()->getCell($column . $row)->getValue();
    }

    /**
     * @inheritdoc
     */
    public function formatedCellValue(string $column, int $row)
    {
        return $this->php_spreadsheet->getActiveSheet()->getCell($column . $row)->getFormattedValue();
    }

    /**
     * @inheritdoc
     */
    public function getHighestRow()
    {
        return $this->php_spreadsheet->getActiveSheet()->getHighestRow();
    }
}
