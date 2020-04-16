<?php
namespace CaT\Plugins\Accomodation\Reservation\Export;

require_once('./libs/composer/vendor/setasign/fpdf/fpdf.php');

/**
 * Extend FPDF to generate accomodation list.
 *
 */
class PDFExport extends \FPDF
{
    const FILE_NAME_SUFFIX = ".pdf";
    const FONTFACE = "Arial";
    const FONTSIZE = 10;

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
     * @var int[]
     */
    protected $widths = array(10,50,70,50,50,50);


    public function __construct(\ilObjAccomodation $object, \CaT\Plugins\Accomodation\ilActions $actions)
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

        $this->buildUserReservationsTable();
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
        $txt = $this->object->getTxtClosure();
        return $txt($code);
    }

    /**
     * @inheritdoc
     */
    public function Header()
    {
        $this->SetFont(self::FONTFACE, 'B', self::FONTSIZE + 2);
        $headline = sprintf(
            utf8_decode($this->txt('reservation_list_headline')),
            utf8_decode($this->course->getTitle())
        );
        $this->Cell(40, 10, $headline);
        $this->Ln(10);
        $this->buildTrainingsInfoTable();
    }

    /**
     * @inheritdoc
     */
    public function Footer()
    {
        $footer = utf8_decode($this->txt('reservation_list_footer_page'))
            . " " . $this->PageNo() . "/{nb}";
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
            $schedule = $this->course->getCourseStart()->get(IL_CAL_FKT_DATE, "d.m.Y")
            . " - "
            . $this->course->getCourseEnd()->get(IL_CAL_FKT_DATE, "d.m.Y");
        }
        list($name, $address) = explode(",", $this->getVenueOfCourse());

        $this->SetFont(self::FONTFACE, '', self::FONTSIZE);
        $this->Cell(40, 5, utf8_decode($this->txt("reservation_list_date")), 0);
        $this->MultiCell(160, 5, $schedule, 0);
        $this->Ln(1);
        $this->Cell(40, 5, utf8_decode($this->txt("reservation_list_location")), 0);
        $this->MultiCell(160, 5, $name . ", " . $address, 0);
        $this->Ln(1);
        $this->Cell(40, 5, utf8_decode($this->txt("reservation_list_trainer")), 0);
        $this->MultiCell(160, 5, str_replace(", ", "\n", $this->getAdminsOfCourse()), 0);
        $this->Ln(10);
    }

    /**
     * Table with reservations.
     * @return void
     */
    protected function buildUserReservationsTable()
    {
        require_once("Modules/Course/classes/class.ilCourseParticipants.php");

        $obj_id = \ilObject::_lookupObjId($this->actions->getObject()->getParentCourseRefId());
        $participants = \ilCourseParticipants::_getInstanceByObjId($obj_id);
        $reservations = array();
        $user_reservations = $this->actions->getAllUserReservationsAtObj();

        foreach ($user_reservations as $ur) {
            if ($participants->isMember($ur->getUserId()) || $participants->isTutor($ur->getUserId())) {
                $user = new \ilObjUser($ur->getUserId());
                $entry = array(
                    $user->getLastName(),
                    $user->getFirstName(),
                    $this->getCourseRoleForId($ur->getUserId(), $participants)
                );

                $note = $ur->getNote();
                $note_value = "-";

                if (!is_null($note)) {
                    $note_value = $note->getNote();
                }


                if (!$ur->hasReservations()) {
                    $entry[] = '-'; // Field reservations
                    $entry[] = '-'; // Field selfpay
                    $entry[] = $note_value; // Field note
                    $reservations[] = $entry;
                } else {
                    $cnt = 1;
                    $dates = array();
                    $selfpay = array();

                    $sort_reservations = $this->sortReservationsByDate($ur->getReservations());

                    foreach ($sort_reservations as $reservation) {
                        $dat = $reservation->getDate()->get(IL_CAL_DATE);

                        $night = $this->actions->formatDate($dat, true);
                        $next = $this->actions->getNextDayLabel($dat);
                        $label = $night . '-' . $next;

                        $dates[] = $cnt . ". " . $label;
                        if ($reservation->getSelfpay()) {
                            $selfpay[] = $cnt . ". " . $label;
                        }
                        $cnt++;
                    }
                    $entry[] = join("\n", $dates);
                    if (empty($selfpay)) {
                        $selfpay = ['-'];
                    }
                    $entry[] = join("\n", $selfpay);

                    $entry[] = $note_value; // Field note

                    $reservations[] = $entry;
                }
            }
        }

        usort($reservations, function ($a, $b) {
            return strcasecmp($a[0], $b[0]);
        });

        $this->addRowHeader();

        foreach ($reservations as $key => $reservation) {
            $this->row(array(
                (string) ($key + 1),
                utf8_decode($reservation[0]),
                utf8_decode($reservation[1]),
                utf8_decode($reservation[2]),
                utf8_decode($reservation[3]),
                utf8_decode($reservation[4])
            ));

            $this->widths = array(10, 270);
            $this->row(array("", $this->txt("reservation_list_note") . ": \n" . utf8_decode($reservation[5])));
            $this->widths = array(10,50,70,50,50,50);
        }
    }

    /**
     * Sort reservation objects by date ascending.
     *
     * @param 	array 	$reservations
     * @return 	array
     */
    protected function sortReservationsByDate(array $reservations)
    {
        usort($reservations, function ($a, $b) {
            return strcmp($a->getDate()->get(IL_CAL_DATE), $b->getDate()->get(IL_CAL_DATE));
        });
        return $reservations;
    }

    /**
     * Table headlines.
     * @return void
     */
    protected function addRowHeader()
    {
        $this->SetFont(self::FONTFACE, 'B', self::FONTSIZE);
        $this->row(array(
            utf8_decode($this->txt("reservation_list_nr")),
            utf8_decode($this->txt("reservation_list_lastname")),
            utf8_decode($this->txt("reservation_list_firstname")),
            utf8_decode($this->txt("reservation_list_function")),
            utf8_decode($this->txt("reservation_list_reservations")),
            utf8_decode($this->txt("reservation_list_selfpay"))
        ));
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
            $old_witdh = $this->widths;
            $this->widths = array(10,50,70,50,50,50);
            $this->addRowHeader();
            $this->widths = $old_witdh;
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
     * Get venue of course.
     *
     * @return string
     */
    protected function getVenueOfCourse()
    {
        $venue = $this->actions->getLocation();

        if ($venue == null) {
            return '';
        }
        return utf8_decode($venue->getHTML(', '));
    }

    /**
     * Get all trainers (tutors) of the course.
     *
     * @return 	string
     */
    protected function getAdminsOfCourse()
    {
        $admin = array();

        foreach ($this->course->getMembersObject()->getAdmins() as $key => $admin_id) {
            $admin[] = utf8_decode(sprintf(
                "%s (%s)",
                \ilObjUser::_lookupFullname($admin_id),
                \ilObjUser::_lookupEmail($admin_id)
            ));
        }
        return implode(", ", $admin);
    }

    /**
     * Get the local role for a usr id.
     *
     * @param 	string 	$id
     * @param 	\ilCourseParticipants
     * @return 	string
     */
    protected function getCourseRoleForId($usr_id, \ilCourseParticipants $participants)
    {
        assert('is_int($usr_id)');

        global $lng;
        $lng->loadLanguageModule('crs');

        if ($participants->isTutor($usr_id)) {
            return $lng->txt('crs_tutor');
        }

        if ($participants->isMember($usr_id)) {
            return $lng->txt('crs_member');
        }
    }
}
