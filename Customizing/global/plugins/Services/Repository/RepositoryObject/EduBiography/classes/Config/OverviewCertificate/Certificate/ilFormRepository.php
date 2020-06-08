<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilFormRepository extends \ilCertificateSettingsFormRepository
{
    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var \ilLanguage
     */
    protected $language;

    /**
     * @var \ilCtrl
     */
    protected $controller;

    /**
     * @var \ilAccess
     */
    protected $access;

    /**
     * @var \ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var \ilCertificatePlaceholderDescription
     */
    protected $placeholderDescriptionObject;

    /**
     * @var \ilPageFormats
     */
    protected $pageFormats;

    /**
     * @var \ilFormFieldParser
     */
    protected $formFieldParser;

    /**
     * @var \ilCertificateTemplateImportAction|null
     */
    protected $importAction;

    /**
     * @var \ilCertificateTemplateRepository
     */
    protected $templateRepository;

    /**
     * @var string
     */
    protected $certificatePath;

    /**
     * @var bool
     */
    protected $hasAdditionalElements;

    /**
     * @var \ilCertificateBackgroundImageFileService
     */
    protected $backGroundImageFileService;

    public function __construct(
        int $objectId,
        string $certificatePath,
        bool $hasAdditionalElements,
        \ilLanguage $language,
        \ilCtrl $controller,
        \ilAccess $access,
        \ilToolbarGUI $toolbar,
        \ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        \ilPageFormats $pageFormats = null,
        \ilFormFieldParser $formFieldParser = null,
        \ilCertificateTemplateImportAction $importAction = null,
        \ilLogger $logger = null,
        \ilCertificateTemplateRepository $templateRepository = null,
        \ILIAS\Filesystem\Filesystem $filesystem = null,
        \ilCertificateBackgroundImageFileService $backgroundImageFileService = null
    ) {
        global $DIC;

        $this->objectId = $objectId;
        $this->language = $language;
        $this->controller = $controller;
        $this->access = $access;
        $this->toolbar = $toolbar;
        $this->placeholderDescriptionObject = $placeholderDescriptionObject;
        $this->certificatePath = $certificatePath;
        $this->hasAdditionalElements = $hasAdditionalElements;

        $database = $DIC->database();


        if (null === $logger) {
            $logger = $logger = $DIC->logger()->cert();
        }

        if (null === $pageFormats) {
            $pageFormats = new \ilPageFormats($language);
        }
        $this->pageFormats = $pageFormats;

        if (null === $formFieldParser) {
            $formFieldParser = new \ilFormFieldParser();
        }
        $this->formFieldParser = $formFieldParser;

        if (null === $importAction) {
            $importAction = new \ilCertificateTemplateImportAction(
                (int) $objectId,
                $certificatePath,
                $placeholderDescriptionObject,
                $logger,
                $DIC->filesystem()->web()
            );
        }
        $this->importAction = $importAction;

        if (null === $templateRepository) {
            $templateRepository = new \ilCertificateTemplateRepository($database, $logger);
        }
        $this->templateRepository = $templateRepository;

        if (null === $filesystem) {
            $filesystem = $DIC->filesystem()->web();
        }

        if (null === $backgroundImageFileService) {
            $backgroundImageFileService = new \ilCertificateBackgroundImageFileService(
                $certificatePath,
                $filesystem
            );
        }
        $this->backGroundImageFileService = $backgroundImageFileService;

        parent::__construct(
            $this->objectId,
            $this->certificatePath,
            $this->hasAdditionalElements,
            $this->language,
            $this->controller,
            $this->access,
            $this->toolbar,
            $this->placeholderDescriptionObject,
            $this->pageFormats,
            $this->formFieldParser,
            $this->importAction,
            $this->logger,
            $this->templateRepository,
            $this->filesystem,
            $this->backgroundImageFileService
        );
    }

    public function createForm(\ilCertificateGUI $certificateGUI)
    {
        $this->controller->setParameter($certificateGUI, "schedule_id", $this->objectId);
        $form = parent::createForm($certificateGUI);
        $form->addCommandButton("cancel", $this->language->txt("back"));
        return $form;
    }
}
