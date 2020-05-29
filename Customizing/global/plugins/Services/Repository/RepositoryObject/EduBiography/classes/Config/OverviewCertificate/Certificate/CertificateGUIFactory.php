<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules;
use ILIAS\Filesystem\Decorator\FilesystemWhitelistDecorator;

class CertificateGUIFactory
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ilLogger
     */
    protected $log;

    /**
     * @var \ilLoggerFactory
     */
    protected $logger_factory;

    /**
     * @var string
     */
    protected $parent_link;

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var Schedules\DB
     */
    protected $schedule_db;

    /**
     * @var ilUserSpecificValues
     */
    protected $user_specific_values;

    public function __construct(
        \ilDBInterface $db,
        \ilLanguage $lng,
        \ilGlobalTemplateInterface $tpl,
        \ilCtrl $ctrl,
        \ilAccess $access,
        \ilToolbarGUI $toolbar,
        \ilLogger $log,
        \ilLoggerFactory $logger_factory,
        string $parent_link,
        \Closure $txt,
        Schedules\DB $schedule_db,
        ilUserSpecificValues $user_specific_values,
        FilesystemWhitelistDecorator $file_system
    ) {
        $this->db = $db;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->toolbar = $toolbar;
        $this->log = $log;
        $this->logger_factory = $logger_factory;
        $this->parent_link = $parent_link;
        $this->txt = $txt;
        $this->schedule_db = $schedule_db;
        $this->user_specific_values = $user_specific_values;
        $this->file_system = $file_system;
    }

    public function getCertificateGUI(int $id)
    {
        require_once __DIR__ . "/class.ilOverviewCertificateGUI.php";
        return new \ilOverviewCertificateGUI(
            $this->getPlaceholder(),
            $this->getPlaceholderValues(),
            $id,
            $this->getPath($id),
            $this->parent_link,
            $this->getFormRepository($id),
            $this->getDeleteAction(),
            $this->getTemplateRepository()
        );
    }

    protected function getPlaceholder() : ilPlaceholderDescription
    {
        return new ilPlaceholderDescription(
            $this->txt,
            $this->getDefaultPlaceholders()
        );
    }

    protected function getPlaceholderValues() : ilPlaceholderValues
    {
        return new ilPlaceholderValues(
            $this->getDefaultPlaceholderValues(),
            $this->schedule_db,
            $this->db,
            $this->getUserSpecificValues()
        );
    }

    protected function getFormRepository(int $id) : ilFormRepository
    {
        return new ilFormRepository(
            $id,
            $this->getPath($id),
            false,
            $this->lng,
            $this->ctrl,
            $this->access,
            $this->toolbar,
            $this->getPlaceholder(),
            null,
            null,
            $this->getImportAction($id),
            $this->log,
            null
        );
    }

    protected function getImportAction(int $id) : \ilCertificateTemplateImportAction
    {
        return new \ilCertificateTemplateImportAction(
            $id,
            $this->getPath($id),
            $this->getPlaceholder(),
            $this->log,
            $this->file_system,
            $this->getTemplateRepository(),
            $this->getObjectHelper()
        );
    }

    protected function getPath(int $id)
    {
        return \ilEduBiographyPlugin::CERTIFICATE_PATH
            . $id
            . '/';
    }

    protected function getTemplateRepository() : \ilCertificateTemplateRepository
    {
        return new ilTemplateRepository(
            $this->db,
            $this->schedule_db,
            $this->logger_factory->getComponentLogger("cert")
        );
    }

    protected function getDeleteAction() : ilTMSCertificateDeleteAction
    {
        return new ilTMSCertificateDeleteAction(
            $this->getTemplateRepository()
        );
    }

    protected function getDefaultPlaceholders() : \ilDefaultPlaceholderDescription
    {
        return new \ilDefaultPlaceholderDescription($this->lng);
    }

    protected function getDefaultPlaceholderValues() : \ilDefaultPlaceholderValues
    {
        return new \ilDefaultPlaceholderValues();
    }

    protected function getUserSpecificValues() : ilUserSpecificValues
    {
        return $this->user_specific_values;
    }

    protected function getObjectHelper() : \ilCertificateObjectHelper
    {
        return new ilObjectHelper();
    }
}
