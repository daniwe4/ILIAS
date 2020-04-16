<?php

declare(strict_types=1);

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;
use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;

/**
 * @ilCtrl_Calls ilCertificateConfigurationGUI: ilOverviewCertificateGUI
 */
class ilCertificateConfigurationGUI
{
    const CMD_SHOW = "certificateeditor";
    const URL_PARAM = "schedule_id";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var string
     */
    protected $parent_link;

    /**
     * @var Schedules\Schedule
     */
    protected $db;

    /**
     * @var Certificate\CertificateGUIFactory
     */
    protected $certificate_gui_factory;

    /**
     * @var string
     */
    protected $certificate_gui_link;

    public function __construct(
        ilCtrl $ctrl,
        ilTemplate $tpl,
        string $parent_link,
        Schedules\DB $db,
        Certificate\CertificateGUIFactory $certificate_gui_factory
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->parent_link = $parent_link;
        $this->db = $db;
        $this->certificate_gui_factory = $certificate_gui_factory;
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case "iloverviewcertificategui":
                    $this->forwardCertificateGUI();
                break;
            default:
                $cmd = $this->ctrl->getCmd();
                switch ($cmd) {
                    case self::CMD_SHOW:
                        break;
                    default:
                        throw new Exception("Unknown command: " . $cmd);
                }
        }
    }

    /**
     * @throws ilCtrlException
     */
    protected function forwardCertificateGUI()
    {
        $id = (int) $_GET[self::URL_PARAM];
        $gui = $this->certificate_gui_factory->getCertificateGUI($id);
        $this->ctrl->forwardCommand($gui);
    }
}
