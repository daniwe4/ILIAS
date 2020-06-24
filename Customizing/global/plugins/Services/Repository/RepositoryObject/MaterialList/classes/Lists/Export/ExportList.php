<?php

namespace CaT\Plugins\MaterialList\Lists\Export;

use \CaT\Libs\ExcelWrapper;

class ExportList
{
    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var SpoutWriter
     */
    protected $spout_writer;

    public function __construct(ExcelWrapper\Writer $spout_writer, ExcelWrapper\Spout\SpoutInterpreter $spout_interpreter, \Closure $txt)
    {
        global $DIC;

        $this->g_ctrl = $DIC->ctrl();

        $this->spout_writer = $spout_writer;
        $this->spout_interpreter = $spout_interpreter;
        $this->txt = $txt;
    }

    /**
     * Start the export system
     *
     * @return null
     */
    public function startExport()
    {
        $this->spout_writer->openFile();
        $this->spout_writer->setMaximumColumnCount(3);
    }

    /**
     * Stop the export
     *
     * @return null
     */
    public function stopExport()
    {
        $this->spout_writer->close();
    }

    /**
     * Print the header part
     *
     * @param [string[]] 	$header_values
     *
     * @return null
     */
    public function printHeader(array $header_values)
    {
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getHeaderStyle()));
        $this->spout_writer->addRow(array($this->txt("xlsx_header")));
        $this->spout_writer->addEmptyRow();
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getSectionHeaderStyle()));
        $this->spout_writer->addRow(array($this->txt("xlsx_general_info_crs")));
        $this->spout_writer->addSeperatorRow();

        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getDefaultStyle()));
        foreach ($header_values as $row) {
            $this->spout_writer->addRow($row);
        }
    }

    /**
     * Print materials
     *
     * @param string 		$material_name
     * @param [string[]]	$materials
     *
     * @return string
     */
    public function printMaterials($material_name, array $materials)
    {
        $this->spout_writer->addEmptyRow();
        $this->spout_writer->addEmptyRow();

        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getSectionHeaderStyle()));
        $this->spout_writer->addRow(array($material_name));
        $this->spout_writer->addSeperatorRow();

        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getTableHeaderStyle()));
        $this->spout_writer->addRow(array($this->txt("xlsx_article_number"), $this->txt("xlsx_title"), $this->txt("xlsx_per_course")));

        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getDefaultStyle()));
        foreach ($materials as $row) {
            $this->spout_writer->addRow($row);
        }
    }

    /**
     * Get the style for header
     *
     * @return Style
     */
    protected function getHeaderStyle()
    {
        if ($this->header_style === null) {
            $this->header_style = (new ExcelWrapper\Style())
                        ->withFontSize(14)
                        ->withBold(true)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->header_style;
    }

    /**
     * Get the style for section header
     *
     * @return Style
     */
    protected function getSectionHeaderStyle()
    {
        if ($this->section_header_style === null) {
            $this->section_header_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(12)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->section_header_style;
    }

    /**
     * Get the style for table header
     *
     * @return Style
     */
    protected function getTableHeaderStyle()
    {
        if ($this->table_header_style === null) {
            $this->table_header_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(10)
                        ->withBold(true)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->table_header_style;
    }

    /**
     * Get the default style
     *
     * @return Style
     */
    protected function getDefaultStyle()
    {
        if ($this->default_style === null) {
            $this->default_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(10)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->default_style;
    }

    /**
     * Translate code to lang value
     *
     * @param string 	$code
     *
     * @return string
     */
    protected function txt(string $code)
    {
        $txt = $this->txt;

        return $txt($code);
    }
}
