<?php


use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\Filter as Filters;
use CaT\Plugins\EduBiography as EduBiography;
use  CaT\Plugins\EduBiography\FileStorage;

class ilEduBiographyReportGUI
{
    const CMD_VIEW = 'report';
    const CMD_EXPORT_XLSX = 'export_xlsx';
    const CMD_DELIVER_CERTIFICATE = "deliverCertificate";
    const CMD_UPGRADE_USR_CERTIFICATES = "upgrade_usr_certificates";

    const P_CRS_ID = "crs_id";
    const P_CERT_USER_ID = "cert_user_id";
    const P_CERT_UPGR_USR_ID = "cer_upgr_usr_id";

    // GOA special hack for #3410
    const GOA20_FILE_NAME = "Zertifikat.pdf";
    public static $import_client = [
        "generali"
    ];
    // GOA special hack for #3410

    /**
     * @var int
     */
    protected $obj_ref_id;

    /**
     * @var	EduBiography\Report
     */
    protected $report;

    /**
     * @var ilEduBiographyPlugin
     */
    protected $plugin;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLogger
     */
    protected $log;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    // GOA special hack for #3410
    /**
     * @var EduBiography\FileStorage\ilCertificateStorage | null
     */
    protected $file_storage;
    // GOA special hack for #3410

    public function __construct(
        ilEduBiographyPlugin $plugin,
        int $obj_ref_id,
        EduBiography\UserOrguLocator $uol,
        EduBiography\Report $report,
        EduBiography\FileStorage\ilCertificateStorage $file_storage = null,
        EduBiography\DetailReportSummary $summary_report = null
    ) {
        $this->obj_ref_id = $obj_ref_id;
        $this->uol = $uol;
        $this->report = $report;
        $this->file_storage = $file_storage;
        $this->summary_report = $summary_report;

        $this->plugin = $plugin;

        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->access = $DIC->access();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->log = $DIC->logger()->root();
        $this->lng = $DIC['lng'];

        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filters\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);

        $this->lgg = new EduBiography\LinkGeneratorGUI($this->ctrl);
        $this->lgg->setReportGui($this);
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd(self::CMD_VIEW);
        $this->cmd = $cmd;
        switch ($cmd) {
            case self::CMD_VIEW:
                if ($this->access->checkAccess("read", "", $this->obj_ref_id)) {
                    $this->renderReport();
                } else {
                    ilUtil::redirect("");
                }
                break;
            case self::CMD_EXPORT_XLSX:
                if ($this->access->checkAccess("read", "", $this->obj_ref_id)) {
                } else {
                    ilUtil::redirect("");
                }
                break;
            case self::CMD_DELIVER_CERTIFICATE:
                $this->deliverCertificate();
                break;
            case self::CMD_UPGRADE_USR_CERTIFICATES:
                $this->upgradeUserCertificates();
                break;
        }
    }

    public function renderReport()
    {
        $filter = $this->configureFilter($this->report);
        $this->enableRelevantParametersCtrl();
        $table = $this->configureTable($this->report);
        $table->setData($this->report->fetchData());

        $sum_table_html = "";
        if (!is_null($this->summary_report)
           && $this->isEduTrackingActive()
        ) {
            $this->configureFilter($this->summary_report);
            $this->enableRelevantParametersCtrl();
            $summary_table = $this->configureTable($this->summary_report);
            $summary_table->setData($this->summary_report->fetchData());
            $sum_table_html = $summary_table->getHTML();
        }

        $this->tpl->setContent(
            $filter->render($this->filter_settings)
            . $sum_table_html
            . $table->getHTML()
        );
        $this->disableRelevantParametersCtrl();
    }

    protected function configureFilter(EduBiography\Report $report)
    {
        if (array_key_exists(EduBiography\Report::KEY_DEFAULT_YEAR_PRESET, $_GET)) {
            $report->setDefaultYear((int) $_GET[EduBiography\Report::KEY_DEFAULT_YEAR_PRESET]);
        }
        $filter = $report->filter();
        $this->filter_settings = $this->loadFilterSettings();

        $display = new Filters\DisplayFilter(new Filters\FilterGUIFactory(), new Filters\TypeFactory());
        $this->ecoded_filter_settigs = $this->encodeFilterParams($this->filter_settings);
        $this->addRelevantParameter('filter_params', $this->ecoded_filter_settigs);

        $report->applyFilterToSpace($display->buildFilterValues($filter, $this->filter_settings));

        require_once("Services/TMS/Filter/classes/class.catFilterFlatViewGUI.php");
        $filter_view = new catFilterFlatViewGUI($this, $filter, $display, $this->cmd);
        return $filter_view;
    }

    protected function loadFilterSettings()
    {
        if (isset($_POST['filter'])) {
            return $_POST['filter'];
        } elseif (isset($_GET['filter_params'])) {
            return $this->decodeFilterParams($_GET['filter_params']);
        }
        return [];
    }

    protected function encodeFilterParams(array $filter_params)
    {
        return base64_encode(json_encode($filter_params));
    }

    protected function decodeFilterParams($encoded_filter)
    {
        return json_decode(base64_decode($encoded_filter), true);
    }

    protected function configureTable(EduBiography\Report $report)
    {
        $this->id = $report->getReportIdentifier() . "_" . $this->obj_ref_id;
        $table = new SelectableReportTableGUI($this, self::CMD_VIEW);
        $report->configureTable($table);
        return $table;
    }

    /**
    * housekeeping the get parameters passed to ctrl
    */
    final public function enableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, $get_value);
        }
    }

    final public function disableRelevantParametersCtrl()
    {
        foreach ($this->relevant_parameters as $get_parameter => $get_value) {
            $this->ctrl->setParameter($this, $get_parameter, null);
        }
    }

    public function addRelevantParameter($key, $value)
    {
        $this->relevant_parameters[$key] = $value;
    }

    /**
     * Delivers certicate for user
     *
     * @return void
     */
    protected function deliverCertificate()
    {
        $get = $_GET;

        $crs_id = (int) $get[self::P_CRS_ID];
        $cert_user_id = (int) $get[self::P_CERT_USER_ID];

        if (!$this->allowedDownload()) {
            ilUtil::sendFailure($this->plugin->txt("no_permission_to_download"), true);
            $this->ctrl->redirect($this);
        }

        // GOA special hack for #3410
        if($crs_id < 0) {
            $file_storage = new FileStorage\ilCertificateStorage($crs_id);
            $file_storage = $file_storage->withUserId($cert_user_id);
            if (
                in_array(CLIENT_ID, self::$import_client) &&
                !is_null(
                    $file_storage->getPathOfCurrentCertificate(
                        self::GOA20_FILE_NAME
                    )
                )
            ) {
                $this->deliverImportedCertificate($file_storage);
                return;
            }
        }
        // GOA special hack for #3410

        $validator = new ilCertificateDownloadValidator();

        if (false === $validator->isCertificateDownloadable($cert_user_id, $crs_id)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }

        $repository = new ilUserCertificateRepository(
            null,
            null,
            ""
        );

        global $DIC;
        $certLogger = $DIC->logger()->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf($cert_user_id, $crs_id);
    }

    // GOA special hack for #3410
    protected function deliverImportedCertificate(FileStorage\ilCertificateStorage $file_storage)
    {
        $path = $file_storage->getPathOfCurrentCertificate(self::GOA20_FILE_NAME);
        \ilUtil::deliverFile($path, self::GOA20_FILE_NAME);
    }
    // GOA special hack for #3410

    /**
     * Check user is allowed to download the certificate
     *
     * @return bool
     */
    protected function allowedDownload()
    {
        return $this->report->allowedDownload();
    }

    protected function isEduTrackingActive()
    {
        return \ilPluginAdmin::isPluginActive('xetr');
    }
}
