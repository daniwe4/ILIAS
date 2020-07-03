<?php

namespace CaT\Plugins\OnlineSeminar\VC\Generic;

use \CaT\Plugins\OnlineSeminar\VC\DataExport;
use \CaT\Libs\ExcelWrapper;

class Export implements DataExport
{
    private static $header_labels = array("Name", "Telefonnummer", "E-Mail", "Firma");
    const FILE_NAME_PREFIX = "GenericExport_";
    const FILE_NAME_SUFFIX = ".xlsx";

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Spout\SpoutWriter
     */
    protected $spout_writer;

    /**
     * @var Spout\SpoutInterpreter
     */
    protected $spout_interpreter;

    /**
     * @var ExcelWrapper\Style
     */
    protected $underline_style;

    public function __construct(ilActions $actions, ExcelWrapper\Spout\SpoutWriter $spout_writer, ExcelWrapper\Spout\SpoutInterpreter $spout_interpreter)
    {
        $this->actions = $actions;
        $this->spout_writer = $spout_writer;
        $this->spout_interpreter = $spout_interpreter;

        $this->file_name = "/" . self::FILE_NAME_PREFIX . $this->actions->getObject()->getParentCourse()->getRefId() . self::FILE_NAME_SUFFIX;
        $this->tmp_folder = sys_get_temp_dir();
        $this->spout_writer->setFileName($this->file_name);
        $this->spout_writer->setPath($this->tmp_folder);
    }

    /**
     * Get path of exportet file
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->tmp_folder . $this->file_name;
    }

    /**
     * Get name of file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Start an export of the VC Data
     *
     * @return null
     */
    public function run()
    {
        $this->startExport();
        $this->printHeader();
        $this->printLines();
        $this->stopExport();
    }

    /**
     * Print the header line
     *
     * @return null
     */
    protected function printHeader()
    {
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getUnderlineStyle()));
        $this->spout_writer->addRow(self::$header_labels);
    }

    /**
     * Prints the user lines
     *
     * @return null
     */
    protected function printLines()
    {
        $participants = $this->actions->getBookedParticipants();
        $this->spout_writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getUnderlineStyle()));

        foreach ($participants as $key => $participant) {
            $row = array();
            $row[] = $participant->getUserName();
            $row[] = $participant->getEmail();
            $row[] = $participant->getPhone();
            $row[] = $participant->getCompany();
            $this->spout_writer->addRow($row);
        }
    }

    /**
     * Start the export system
     *
     * @return null
     */
    protected function startExport()
    {
        $this->spout_writer->openFile();
        $this->spout_writer->setMaximumColumnCount(8);
    }

    /**
     * Stop the export
     *
     * @return null
     */
    protected function stopExport()
    {
        $this->spout_writer->close();
    }

    /**
     * Get the style for underline text
     *
     * @return Style
     */
    protected function getUnderlineStyle()
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
}
