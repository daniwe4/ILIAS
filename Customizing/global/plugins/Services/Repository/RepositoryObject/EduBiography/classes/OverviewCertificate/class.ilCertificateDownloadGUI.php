<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\OverviewCertificate;
use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;
use CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;
use CaT\Plugins\EduBiography\Config\OverviewCertificate\ParticipationDocument\ilFileStorage;
use  CaT\Plugins\EduBiography\ParticipationDocument;

class ilCertificateDownloadGUI
{
    const CMD_SHOW_CERTIFICATES = "showCertificates";
    const CMD_DOWNLOAD_CERTIFICATE = "downloadCertificate";
    const CMD_DOWNLOAD_PART_DOCUMENT = "downloadPartDocument";

    const SCHEDULE_ID = "schedule_id";
    const RECEIVED_IDD_MIN = "received_idd_min";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var OverviewCertificate\ilCertificateTableGUI
     */
    protected $certificate_table;

    /**
     * @var Schedules\DB
     */
    protected $schedule_db;

    /**
     * @var Certificate\ilUserSpecificValues
     */
    protected $user_specific_values;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var OverviewCertificate\ilCertificateHandling
     */
    protected $certificate_handling;

    /**
     * @var OverviewCertificate\DB
     */
    protected $db;

    /**
     * @var ParticipationDocument\Generator
     */
    protected $pdf_generator;

    /**
     * @var ilFileStorage
     */
    protected $file_system;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        Closure $txt,
        OverviewCertificate\ilCertificateTableGUI $certificate_table,
        Schedules\DB $schedule_db,
        Certificate\ilUserSpecificValues $user_specific_values,
        ilObjUser $user,
        OverviewCertificate\ilCertificateHandling $certificate_handling,
        OverviewCertificate\DB $db,
        ParticipationDocument\Generator $pdf_generator,
        ilFileStorage $file_system
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->txt = $txt;
        $this->certificate_table = $certificate_table;
        $this->schedule_db = $schedule_db;
        $this->user_specific_values = $user_specific_values;
        $this->user = $user;
        $this->certificate_handling = $certificate_handling;
        $this->db = $db;
        $this->pdf_generator = $pdf_generator;
        $this->file_system = $file_system;
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW_CERTIFICATES:
                $this->showCertificates();
                break;
            case self::CMD_DOWNLOAD_CERTIFICATE:
                $this->downloadCertificate();
                break;
            case self::CMD_DOWNLOAD_PART_DOCUMENT:
                $this->downloadPartDocument();
                break;
            default:
                throw new Exception("Unknown command: " . $cmd);
        }
    }

    protected function showCertificates()
    {
        $current_certificate = $this->db->selectFor((int) $this->user->getId());
        $current_certificate_ids = array_keys($current_certificate);

        $schedules = $this->schedule_db->getAllActiveScheduled($current_certificate_ids);
        $data = [];

        foreach ($schedules as $schedule) {
            $show_overview_download = true;
            if(
                ! in_array($schedule->getId(), $current_certificate_ids) &&
                ! $schedule->isActive()
            ) {
                $show_overview_download = false;
            }

            $received_idd_min = $this->user_specific_values->getIDDTimesFor(
                (int)$this->user->getId(),
                $schedule->getStart(),
                $schedule->getEnd()
            );

            if (
                $received_idd_min == 0 &&
                !$schedule->isParticipationsDocumentActive()
            ) {
                continue;
            }
            $data[] =
                new CaT\Plugins\EduBiography\OverviewCertificate\Certificate(
                    $schedule->getId(),
                    $schedule->getTitle(),
                    $schedule->getStart(),
                    $schedule->getEnd(),
                    $schedule->getMinIddValue(),
                    $received_idd_min,
                    $schedule->isParticipationsDocumentActive(),
                    $show_overview_download
                )
            ;
        }

        $this->certificate_table->setData($data);
        $this->tpl->setContent($this->certificate_table->render());
    }

    protected function downloadCertificate()
    {
        $schedule_id = (int) $_GET[self::SCHEDULE_ID];
        $received_idd_min = (int) $_GET[self::RECEIVED_IDD_MIN];
        $usr_id = (int) $this->user->getId();

        try {
            $this->certificate_handling->getCurrentCertificate($usr_id, $schedule_id);
        } catch (ilException $e) {
            $this->certificate_handling->createUserCertificate($usr_id, $schedule_id);
            $this->db->save($usr_id, $schedule_id, $received_idd_min);
            $this->redirectForDelivering($schedule_id, $received_idd_min);
        }

        $this->certificate_handling->deliverCertificate(
            $usr_id,
            $schedule_id,
            $this->txt('error_creating_certificate_pdf')
        );
    }

    protected function redirectForDelivering($id, $received_idd_min)
    {
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, $id);
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::RECEIVED_IDD_MIN, $received_idd_min);
        $link = $this->ctrl->getLinkTargetByClass(
            "ilCertificateDownloadGUI",
            "downloadCertificate",
            "",
            true,
            false
        );
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::SCHEDULE_ID, null);
        $this->ctrl->setParameterByClass("ilCertificateDownloadGUI", \ilCertificateDownloadGUI::RECEIVED_IDD_MIN, null);

        $this->ctrl->redirectToURL($link);
    }

    protected function downloadPartDocument()
    {
        $schedule_id = (int) $_GET[self::SCHEDULE_ID];
        $schedule = $this->schedule_db->selectFor($schedule_id);
        $received_idd_min = $this->user_specific_values->getIDDTimesFor(
            (int) $this->user->getId(),
            $schedule->getStart(),
            $schedule->getEnd()
        );

        list($file_path, $file_name) = $this->pdf_generator->createPdf(
            (int) $this->user->getId(),
            $schedule,
            $received_idd_min,
            $this->file_system->getIncludePath()
        );
        ilUtil::deliverFile(
            $file_path,
            $file_name,
            'application/pdf'
        );
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
