<?php

namespace CaT\Plugins\OnlineSeminar\VC\CSN;

use \CaT\Plugins\OnlineSeminar\VC\DataExport;
use \CaT\Libs\ExcelWrapper;

class Export implements DataExport
{
    private static $header_labels = array("Nachname", "Telefon 1 (geschaeftlich)", "E-Mail");
    const FILE_NAME_PREFIX = "CSNExport_";
    const FILE_NAME_SUFFIX = ".csv";
    const FIELD_DELIMITER = ";";

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var ExcelWrapper\Writer
     */
    protected $writer;

    /**
     * @var Spout\SpoutInterpreter
     */
    protected $spout_interpreter;

    /**
     * @var ExcelWrapper\Style
     */
    protected $underline_style;

    public function __construct(ilActions $actions, ExcelWrapper\Writer $writer, ExcelWrapper\Spout\SpoutInterpreter $spout_interpreter)
    {
        $this->actions = $actions;
        $this->writer = $writer;
        $this->spout_interpreter = $spout_interpreter;

        $this->file_name = "/" . self::FILE_NAME_PREFIX . $this->actions->getObject()->getParentCourse()->getRefId() . self::FILE_NAME_SUFFIX;
        $this->tmp_folder = sys_get_temp_dir();
        $this->writer->setFileName($this->file_name);
        $this->writer->setPath($this->tmp_folder);
        $this->writer->setFieldDelimiter(self::FIELD_DELIMITER);
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
        $this->writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getUnderlineStyle()));
        $this->writer->addRow(self::$header_labels);
    }

    /**
     * Prints the user lines
     *
     * @return null
     */
    protected function printLines()
    {
        $participants = $this->actions->getBookedParticipants();
        $this->writer->setColumnStyle("A", $this->spout_interpreter->interpret($this->getUnderlineStyle()));

        require_once("Services/User/classes/class.ilObjUser.php");
        foreach ($participants as $key => $participant) {
            $user = new \ilObjUser($participant->getUserId());
            $row = array();
            $row[] = $participant->getUserName();
            $row[] = " " . $participant->getPhone();
            $row[] = $participant->getEmail();
            $this->writer->addRow($row);
        }
    }

    /**
     * Start the export system
     *
     * @return null
     */
    protected function startExport()
    {
        $this->writer->openFile();
    }

    /**
     * Stop the export
     *
     * @return null
     */
    protected function stopExport()
    {
        $this->writer->close();
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
