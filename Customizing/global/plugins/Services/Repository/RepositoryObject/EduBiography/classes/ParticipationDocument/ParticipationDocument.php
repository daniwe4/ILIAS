<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\Schedule;

class ParticipationDocument extends \FPDF
{
    const FILE_NAME_SUFFIX = ".pdf";
    const FONT_FACE = "Arial";
    const FONT_SIZE = 10;

    const HEADLINE_WIDTH = 65;
    const HEADER_PAGE_ONE_WIDTH = 45;
    const LINE_HEIGHT = 8;
    const DESCRIPTION_WIDTH = 270;
    const FOOTER_Y = -10;
    const FOOTER_COLUMN_WIDTH = 90;
    const PAGE_BREAK_POINT = 190;
    const CONTINUATION_TABLE_Y = 50.00125;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var Schedule
     */
    protected $schedule;

    /**
     * @var int
     */
    protected $received_idd_min;

    /**
     * @var string
     */
    protected $logo_path;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var array
     */
    protected $widths = [90, 30, 25, 55, 45, 25];
    /**
     * @var array
     */
    protected $aligns = ["L", "L", "L", "L", "L", "R"];

    public function __construct(
        int $usr_id,
        Schedule $schedule,
        int $received_idd_min,
        string $logo_path,
        \Closure $txt,
        $orientation = 'P',
        $unit = 'mm',
        $size = 'A4'
    ) {
        $this->usr_id = $usr_id;
        $this->schedule = $schedule;
        $this->received_idd_min = $received_idd_min;
        $this->logo_path = $logo_path;
        $this->txt = $txt;
        parent::__construct($orientation, $unit, $size);
    }

    public function createPdf(
        string $file_path,
        array $participations
    ) {
        $this->addHeadline();
        $this->addLogo($this->logo_path);
        $this->addPageOneHeader();
        $this->addDescription();
        $this->addTableHeader();
        $this->addTable($participations);

        $this->Output('F', $file_path);
    }

    public function getFileName() : string
    {
        return $this->txt("part_document_file_name") . self::FILE_NAME_SUFFIX;
    }

    public function buildTempFolder()
    {
        $tmp_folder = sys_get_temp_dir();
        if (substr($tmp_folder, -1) != "/") {
            $tmp_folder .= "/";
        }
        $tmp_folder .= uniqid() . '/';
        mkdir($tmp_folder, 0700, true);
        return $tmp_folder;
    }

    protected function addHeadline()
    {
        $this->SetFont(self::FONT_FACE, 'B', self::FONT_SIZE + 2);
        $this->Cell(
            self::HEADLINE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText($this->txt("part_document_headline")),
            0
        );
        $this->Ln(self::LINE_HEIGHT);
    }

    protected function addLogo(string $path)
    {
        if ($path != "") {
            $this->Image($path, 240, 10, 40);
        }
    }

    protected function addPageOneHeader()
    {
        $name = \ilObjUser::_lookupName($this->usr_id);
        $this->SetFont(self::FONT_FACE, '', self::FONT_SIZE);
        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText($this->txt("part_document_name")),
            0
        );
        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText($name["title"] . " " . $name["firstname"] . " " . $name["lastname"]),
            0
        );
        $this->Ln(self::LINE_HEIGHT);

        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText($this->txt("part_document_schedule")),
            0
        );
        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText(
                $this->schedule->getStart()->format("d.m.Y")
                . " - "
                . $this->schedule->getEnd()->format("d.m.Y")
            ),
            0
        );
        $this->Ln(self::LINE_HEIGHT);

        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText($this->txt("part_document_idd_minutes")),
            0
        );
        $this->Cell(
            self::HEADER_PAGE_ONE_WIDTH,
            self::LINE_HEIGHT,
            $this->decodeText(
                $this->minutesToString($this->received_idd_min)
                . " "
                . $this->txt("part_document_hours")
            ),
            0
        );
        $this->Ln(self::LINE_HEIGHT);
    }

    protected function addDescription()
    {
        $this->SetFont(self::FONT_FACE, '', self::FONT_SIZE);
        $this->Ln(self::LINE_HEIGHT);
        $this->MultiCell(
            self::DESCRIPTION_WIDTH,
            self::LINE_HEIGHT - 2,
            $this->decodeText($this->txt("part_document_description")),
            0
        );

        $this->Ln(self::LINE_HEIGHT / 2);
    }

    protected function addTableHeader()
    {
        $this->SetFont(self::FONT_FACE, 'B', self::FONT_SIZE);
        $row = [
            $this->decodeText($this->txt("part_document_crs_title")),
            $this->decodeText($this->txt("part_document_crs_type")),
            $this->decodeText($this->txt("part_document_crs_schedule")),
            $this->decodeText($this->txt("part_document_crs_content")),
            $this->decodeText($this->txt("part_document_crs_provider")),
            $this->decodeText($this->txt("part_document_crs_idd")),
        ];
        $this->row($row);
    }

    /**
     * @param Participation[] $participations
     */
    protected function addTable(array $participations)
    {
        $this->SetFont(self::FONT_FACE, '', self::FONT_SIZE);
        foreach ($participations as $participation) {
            $row = [
                $this->decodeText($participation->getTitle()),
                $this->decodeText($participation->getType()),
                $this->decodeText(
                    $participation->getBeginDate()->format("d.m.Y")
                    . "- "
                    . $participation->getEndDate()->format("d.m.Y")
                ),
                $this->decodeText($this->valueOrPlaceholder($participation->getContent())),
                $this->decodeText($this->valueOrPlaceholder($participation->getProvider())),
                $this->decodeText(
                    $this->minutesToString($participation->getIddMinutes())
                    . " "
                    . $this->txt("part_document_hours")
                )
            ];
            $this->row($row);
        }
    }

    public function Footer()
    {
        $usr_id = $this->usr_id;
        $page = $this->PageNo();
        $this->SetY(self::FOOTER_Y);
        $this->SetFont(self::FONT_FACE, '', self::FONT_SIZE - 2);

        $dt = new \DateTime();
        $day = $dt->format("d");
        $month = \ilCalendarUtil::_numericMonthToString($dt->format("n"), true);
        $rest = $dt->format("Y, H:i");
        $this->Cell(
            self::FOOTER_COLUMN_WIDTH,
            self::LINE_HEIGHT / 2,
            $this->decodeText(
                $this->txt("part_document_print_date")
                . " "
                . $day
                . " "
                . $month
                . " "
                . $rest
                . " "
                . $this->txt("part_document_clock")
            ),
            0
        );
        $this->Cell(
            self::FOOTER_COLUMN_WIDTH,
            self::LINE_HEIGHT / 2,
            $this->decodeText(
                $this->txt("part_document_user_id")
                . " "
                . $usr_id
            ),
            0,
            0,
            'C'
        );
        $this->Cell(
            self::FOOTER_COLUMN_WIDTH,
            self::LINE_HEIGHT / 2,
            $this->decodeText(
                $this->txt("part_document_page_number")
                . " "
                . $page
            ),
            0,
            0,
            'R'
        );
    }

    protected function minutesToString(int $minutes) : string
    {
        $hh = floor($minutes / 60);
        $mm = $minutes - $hh * 60;

        return str_pad((string) $hh, 2, "0", STR_PAD_LEFT)
            . ":"
            . str_pad((string) $mm, 2, "0", STR_PAD_LEFT);
    }

    protected function valueOrPlaceholder(string $value = null) : string
    {
        if (
            is_null($value) ||
            trim($value) == ""
        ) {
            return "-";
        }

        return $value;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

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
        if (
            ($this->GetY() + $h) >
            self::PAGE_BREAK_POINT
        ) {
            $this->AddPage($this->CurOrientation);
            $this->addHeadline();
            $this->addLogo($this->logo_path);
            $this->addPageOneHeader();
            $this->setY(self::CONTINUATION_TABLE_Y);
            $this->addTableHeader();
            $this->SetFont(self::FONT_FACE, '', self::FONT_SIZE);
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

    protected function decodeText(string $value) : string
    {
        return utf8_decode(
            html_entity_decode(
                str_replace('&shy;', '', $value)
            )
        );
    }
}
