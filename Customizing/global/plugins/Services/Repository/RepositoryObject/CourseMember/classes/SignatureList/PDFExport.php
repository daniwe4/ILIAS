<?php
namespace CaT\Plugins\CourseMember\SignatureList;

require_once('./libs/composer/vendor/setasign/fpdf/fpdf.php');

/**
 * Extend FPDF to generate signature list.
 *
 */
class PDFExport extends \FPDF
{
    use ilExportDataHelpers;

    const FILE_NAME_SUFFIX = ".pdf";
    const FONTFACE = "Arial";
    const FONTSIZE = 10;

    const ADDITIONAL_MEMBER_LINES = 5;

    /**
     * @var \ilObjCourseMember
     */
    protected $object;

    /**
     * @var \ilObjCourse
     */
    protected $course;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var string
     */
    protected $tmp_folder;

    /**
     * Column-widths of the participants table
     * will be re-calculated in ::buildParticipantsTable
     * @var int[]
     */
    protected $widths = array(10,55,55,70,80);


    public function __construct(\ilObjCourseMember $object, ilActions $actions)
    {
        $this->object = $object;
        $this->course = $object->getParentCourse();
        $this->actions = $actions;

        $this->tmp_folder = $this->buildTempFolder();
        $dat_part = '';
        if (!is_null($this->course->getCourseStart())) {
            $dat_part = "_" . $this->course->getCourseStart()->get(IL_CAL_FKT_DATE, "d.m.Y");
        }
        $this->file_name = ""
            . $this->course->getRefId()
            . "_"
            . $this->stripTitleForFilename($this->course->getTitle())
            . "_"
            . $this->txt("list")
            . $dat_part
            . self::FILE_NAME_SUFFIX;

        parent::__construct();
        $this->AliasNbPages();
        $this->AddPage('L');
        $this->SetFont(self::FONTFACE, '', self::FONTSIZE);

        $this->buildParticipantsTable();
    }

    /**
     * Build a unique temporary folder.
     *
     * @return 	string
     */
    protected function buildTempFolder()
    {
        $tmp_folder = sys_get_temp_dir();
        if (substr($tmp_folder, -1) != "/") {
            $tmp_folder .= "/";
        }
        $tmp_folder .= uniqid() . '/';
        mkdir($tmp_folder, 0700, true);
        return $tmp_folder;
    }

    /**
     * Strip all unwanted chars from title.
     * @param 	string $title
     * @return 	string
     */
    private function stripTitleForFilename($title)
    {
        return preg_replace('/[^a-zA-Z0-9\_\-äöüÄÖÜß]/', '', $title);
    }

    /**
     * Write PDF to file.
     *
     * @return void
     */
    public function writeOutput()
    {
        $this->Output('F', $this->getFilePath());
    }

    /**
     * Get path of exported file
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
     * Translate lang code to text
     *
     * @param string 	$code
     * @return string 	$text
     */
    protected function txt($code)
    {
        return $this->object->pluginTxt($code);
    }

    /**
     * @inheritdoc
     */
    public function Header()
    {
        $img = $this->actions->getPath();
        if ($img) {
            $this->Image($img, 240, 10, 40);
        }

        $this->SetFont(self::FONTFACE, 'B', self::FONTSIZE + 2);
        $headline = $this->decodeText(
            sprintf(
                $this->txt('signaturelist_headline'),
                $this->course->getTitle()
            )
        );
        $this->Cell(30, 10, $headline);
        $this->Ln(10);
        $this->buildTrainingsInfoTable();
    }

    /**
     * @inheritdoc
     */
    public function Footer()
    {
        $footer = $this->decodeText(
            $this->txt('signaturelist_footer_page')
            . " " . $this->PageNo() . "/{nb}"
        );
        $this->SetY(-15);
        $this->SetFont(self::FONTFACE, 'B', self::FONTSIZE - 2);
        $this->Cell(0, 10, $footer, 0, 0, 'C');
    }

    /**
     * Table with trainings info.
     * @return void
     */
    protected function buildTrainingsInfoTable()
    {
        $schedule = '';
        if (!is_null($this->course->getCourseStart()) && !is_null($this->course->getCourseEnd())) {
            $schedule = $this->decodeText(
                $this->course->getCourseStart()->get(IL_CAL_FKT_DATE, "d.m.Y")
                . " - "
                . $this->course->getCourseEnd()->get(IL_CAL_FKT_DATE, "d.m.Y")
            );
        }

        $this->SetFont(self::FONTFACE, '', self::FONTSIZE);
        $this->Cell(40, 5, $this->decodeText($this->txt("signaturelist_date")), 0);
        $this->MultiCell(160, 5, $schedule, 0);
        $this->Ln(1);
        $this->Cell(40, 5, $this->decodeText($this->txt("signaturelist_location")), 0);
        $this->MultiCell(160, 5, $this->getVenueOfCourse(), 0);
        $this->Ln(1);
        $this->Cell(40, 5, $this->decodeText($this->txt("signaturelist_trainer")), 0);
        $this->MultiCell(160, 5, $this->decodeText($this->getTrainersOfCourse($this->course)), 0);
        $this->Ln(5);
        $this->Cell(40, 5, $this->decodeText($this->txt("signature_trainer")), 0);
        $this->Ln(10);
        $this->Cell(40, 5, $this->decodeText($this->txt("signature_line")), 0);
        $this->Ln(10);
    }

    /**
     * Table with participants.
     * @return void
     */
    protected function buildParticipantsTable()
    {
        $settings = $this->object->getSettings();
        $num_cols = 1;
        if ($settings->getListOptionOrgu()) {
            $num_cols += 1;
        }
        if ($settings->getListOptionText()) {
            $num_cols += 1;
        }

        $participants = array();
        foreach ($this->course->getMembersObject()->getMembers() as $key => $member) {
            $user = \ilObjectFactory::getInstanceByObjId($member);

            $entry = array(
                $user->getLastname(),
                $user->getFirstname(),
            );
            if ($settings->getListOptionOrgu()) {
                $entry[] = $this->getOrgUnitOf($user->getId());
            }
            if ($settings->getListOptionText()) {
                $entry[] = '';
            }

            $participants[] = $entry;
        }
        usort($participants, function ($a, $b) {
            return strcmp($a[0], $b[0]);
        });


        //adjust widths: the last (potentially three) cols should share 150 units.
        $this->widths = array(10,55,55);
        foreach (range(1, $num_cols) as $i) {
            $this->widths[] = 150 / $num_cols;
        }

        $this->addRowHeader();

        foreach ($participants as $key => $participant) {
            $row = array((string) $key + 1);
            foreach ($participant as $value) {
                $row[] = $this->decodeText($value);
            }
            $row[] = ''; // free space for signature;
            $this->row($row);
        }

        // Additional lines for people, who were not registered via ILIAS
        $add_row = array_fill(0, count($this->widths), '');
        for ($count = 0; $count < self::ADDITIONAL_MEMBER_LINES; $count++) {
            $this->row($add_row);
        }
    }

    /**
     * Table headlines.
     * @return void
     */
    protected function addRowHeader()
    {
        $this->SetFont(self::FONTFACE, 'B', self::FONTSIZE);
        $header = array(
            $this->decodeText($this->txt("signaturelist_nr")),
            $this->decodeText($this->txt("lastname")),
            $this->decodeText($this->txt("firstname"))
        );

        $settings = $this->object->getSettings();
        if ($settings->getListOptionOrgu()) {
            $header[] = $this->decodeText($this->txt("signaturelist_orgus"));
        }
        if ($settings->getListOptionText()) {
            $header[] = $this->decodeText($this->txt("signaturelist_freetext"));
        }

        $header[] = $this->decodeText($this->txt("signaturelist_signature"));

        $this->row($header);
        $this->SetFont(self::FONTFACE, '', self::FONTSIZE);
    }

    /**
     * write a table-row.
     * @param string[]
     * @return void
     */
    public function row($data)
    {
        //Calculate the height of the row
        $nb = 0;
        for ($i = 0;$i < count($data);$i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 5 * $nb;
        $h = ($h < 10) ? 10 : $h;
        //Issue a page break first if needed
        $this->checkPageBreak($h);
        //Draw the cells of the row
        for ($i = 0;$i < count($data);$i++) {
            $w = $this->widths[$i];
            $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x = $this->GetX();
            $y = $this->GetY();
            //Draw the border
            $this->Rect($x, $y, $w, $h);
            //Print the text
            $this->MultiCell($w, 5, $data[$i], 0, $a);
            //Put the position to the right of the cell
            $this->SetXY($x + $w, $y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    /**
     * If the height h would cause an overflow, add a new page immediately
     * @param 	int 	$h
     * @return 	void
     */
    public function checkPageBreak($h)
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
            $this->addRowHeader();
        }
    }

    /**
     * @inheritdoc
     */
    public function NbLines($w, $txt)
    {
        //Computes the number of lines a MultiCell of width w will take
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 and $s[$nb - 1] == "\n") {
            $nb--;
        }
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') {
                $sep = $i;
            }
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) {
                        $i++;
                    }
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    /**
     * Get OrgUnit of user.
     *
     * @param int 	$user_id
     * @return string
     */
    protected function getOrgUnitOf($user_id)
    {
        return \ilObjUser::lookupOrgUnitsRepresentation($user_id);
    }

    /**
     * Get venue of course.
     *
     * @return string
     */
    protected function getVenueOfCourse()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if (\ilPluginAdmin::isPluginActive('venues')) {
            $vplug = \ilPluginAdmin::getPluginObjectById('venues');
            list($venue_id, $city, $address, $name, $postcode) = $vplug->getVenueInfos($this->course->getId());
            $ext = [];
            if ($name != "") {
                $ext[] = $name;
            }
            if ($city != "") {
                $ext[] = $city;
            }

            if (count($ext) == 2) {
                $t = join(", ", $ext);
            } elseif (count($ext) == 1) {
                $t = $ext[0];
            }
            return $this->decodeText($t);
        }
        return '';
    }

    protected function decodeText($text)
    {
        return utf8_decode($text);
    }
}
