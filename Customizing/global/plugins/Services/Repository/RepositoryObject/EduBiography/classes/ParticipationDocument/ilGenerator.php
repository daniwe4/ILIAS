<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\Schedule;

class ilGenerator implements Generator
{
    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var DB
     */
    protected $db;

    public function __construct(\Closure $txt, DB $db)
    {
        $this->txt = $txt;
        $this->db = $db;
    }

    public function createPdf(
        int $user_id,
        Schedule $schedule,
        int $received_idd_min,
        string $logo_path
    ) : array {
        $document = new ParticipationDocument(
            $user_id,
            $schedule,
            $received_idd_min,
            $logo_path,
            $this->txt,
            'L'
        );
        $document->AliasNbPages();
        $document->AddPage();
        $folder = $document->buildTempFolder();
        $file_name = $document->getFileName();
        $file_path = $folder . "/" . $file_name;

        $document->createPdf(
            $file_path,
            $this->db->getSuccessfulCourseInformationsFor(
                $user_id,
                $schedule->getStart(),
                $schedule->getEnd()
            )
        );
        return [$file_path, $file_name];
    }
}
