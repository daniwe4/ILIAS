<?php

declare(strict_types=1);

namespace CaT\Plugins\Accounting\Data\Export;

use \CaT\Libs\ExcelWrapper;

/**
 * Class for Excel export
 *
 * @author Daniel Weise 	<daniel.weise@concepts-and-training.de>
 */
class ExportData
{
    /**
     * @var string
     */
    protected $file_name;
    /**
     * @var string
     */
    protected $tmp_folder;
    /**
     * @var ExcelWrapper\Writer
     */
    protected $spout_writer;
    /**
     * @var ExcelWrapper\Spout\SpoutInterpreter
     */
    protected $spout_interpreter;
    /**
     * @var \Closure
     */
    protected $txt;
    /**
     * @var ExcelWrapper\Style
     */
    protected $header_style;
    /**
     * @var ExcelWrapper\Style
     */
    protected $bold_style;
    /**
     * @var ExcelWrapper\Style
     */
    protected $underline_style;
    /**
     * @var ExcelWrapper\Style
     */
    protected $default_style;

    public function __construct(
        string $file_name,
        string $tmp_folder,
        ExcelWrapper\Writer $spout_writer,
        ExcelWrapper\Spout\SpoutInterpreter $spout_interpreter,
        \Closure $txt
    ) {
        $this->file_name = $file_name;
        $this->tmp_folder = $tmp_folder;
        $this->spout_writer = $spout_writer;
        $this->spout_interpreter = $spout_interpreter;
        $this->txt = $txt;
    }

    /**
     * @param string[] 	$table_headers
     * @param string[] 	$export_data
     * @throws \Exception if filename or tmpfolder is not set
     */
    public function run(array $table_headers, string $crs_title, string $crs_date, array $export_data)
    {
        if (is_null($this->file_name) || is_null($this->tmp_folder)) {
            throw new \Exception("No filename or tmp folder set");
        }

        $this->startExport();
        $this->printHeader($table_headers, $crs_title, $crs_date);
        $this->printData($export_data);
        $this->stopExport();
        $this->deliver();
    }

    protected function startExport()
    {
        $this->spout_writer->setFileName($this->file_name);
        $this->spout_writer->setPath($this->tmp_folder);
        $this->spout_writer->openFile();
        $this->spout_writer->setMaximumColumnCount(10);
    }

    protected function stopExport()
    {
        $this->spout_writer->close();
    }

    protected function deliver()
    {
        \ilUtil::deliverFile(
            $this->tmp_folder . $this->file_name,
            $this->file_name,
            '',
            false,
            true
        );
    }

    /**
     * @param string[] 	$header_values
     */
    protected function printHeader(array $header_values, string $crs_title, string $date)
    {
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getHeaderStyle()));
        $this->spout_writer->addRow(array($this->txt("xacc_xlsx_header")));
        $this->spout_writer->addEmptyRow();
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getDefaultStyle()));
        $this->spout_writer->addRow(array($this->txt("xacc_xlsx_for_training")));
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getBoldStyle()));
        $this->spout_writer->addRow(array($this->txt("xacc_xlsx_crs_title"), $crs_title));
        $this->spout_writer->addRow(array($this->txt("xacc_xlsx_date"), $date));
        
        $this->spout_writer->addEmptyRow();
        $this->spout_writer->addEmptyRow();
        $this->spout_writer->addEmptyRow();

        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getBoldStyle()));
        $this->spout_writer->addRow($header_values);
    }

    /**
     * @param string[] 	$data
     */
    protected function printData(array $data)
    {
        $sum = 0.0;
        $sum_gross = 0.0;
        $this->spout_writer->addSeperatorRow();
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getDefaultStyle()));
        foreach ($data as $dat) {
            $this->spout_writer->addRow($dat);
            $sum += $dat[7];
            $sum_gross += $dat[9];
        }
        $this->spout_writer->addSeperatorRow();
        $this->spout_writer->addRow(array("","","","","","","",$sum,"", $sum_gross));
    }

    protected function getHeaderStyle() : ExcelWrapper\Style
    {
        if ($this->header_style === null) {
            $this->header_style = (new ExcelWrapper\Style())
                        ->withFontSize(12)
                        ->withBold(true)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->header_style;
    }

    protected function getBoldStyle() : ExcelWrapper\Style
    {
        if ($this->bold_style === null) {
            $this->bold_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(10)
                        ->withBold(true)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->bold_style;
    }

    protected function getUnderlineStyle() : ExcelWrapper\Style
    {
        if ($this->underline_style === null) {
            $this->underline_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(10)
                        ->withUnderline(true)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->underline_style;
    }

    protected function getDefaultStyle() : ExcelWrapper\Style
    {
        if ($this->default_style === null) {
            $this->default_style = (new ExcelWrapper\Style())
                        ->withFontFamily('Arial')
                        ->withFontSize(10)
                        ->withOrientation(ExcelWrapper\Style::ORIENTATION_LEFT);
        }

        return $this->default_style;
    }

    protected function withFileName(string $file_name)
    {
        $clone = clone $this;
        $clone->file_name = $file_name;
        return $clone;
    }

    protected function withTmpFolder(string $tmp_folder)
    {
        $clone = clone $this;
        $clone->tmp_folder = $tmp_folder;
        return $clone;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
