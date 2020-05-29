<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilTMSCertificateDeleteAction extends \ilCertificateTemplateDeleteAction
{
    /**
     * @var \ilCertificateTemplateRepository
     */
    protected $templateRepository;

    /**
     * @var string
     */
    protected $rootDirectory;

    /**
     * @var \ilCertificateUtilHelper|null
     */
    protected $utilHelper;

    /**
     * @var \ilCertificateObjectHelper|null
     */
    protected $objectHelper;

    /**
     * @var string
     */
    protected $iliasVersion;

    /**
     * @param \ilCertificateTemplateRepository $templateRepository
     * @param string $rootDirectory
     * @param \ilCertificateUtilHelper|null $utilHelper
     * @param \ilCertificateObjectHelper|null $objectHelper
     * @param string $iliasVersion
     */
    public function __construct(
        \ilCertificateTemplateRepository $templateRepository,
        string $rootDirectory = CLIENT_WEB_DIR,
        \ilCertificateUtilHelper $utilHelper = null,
        \ilCertificateObjectHelper $objectHelper = null,
        $iliasVersion = ILIAS_VERSION_NUMERIC
    ) {
        $this->templateRepository = $templateRepository;

        $this->rootDirectory = $rootDirectory;

        if (null === $utilHelper) {
            $utilHelper = new \ilCertificateUtilHelper();
        }
        $this->utilHelper = $utilHelper;

        if (null === $objectHelper) {
            $objectHelper = new \ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        $this->iliasVersion = $iliasVersion;
    }

    /**
     * @param $templateTemplateId
     * @param $objectId
     * @param string $iliasVersion
     * @return mixed
     * @throws \ilDatabaseException
     */
    public function delete($templateTemplateId, $objectId)
    {
        $template = $this->templateRepository->fetchCurrentlyUsedCertificate($objectId);

        $this->templateRepository->deleteTemplate($templateTemplateId, $objectId);

        $version = (int) $template->getVersion();
        $certificateTemplate = new \ilCertificateTemplate(
            $objectId,
            "xebr",
            '',
            hash('sha256', ''),
            '',
            $version + 1,
            $this->iliasVersion,
            time(),
            false,
            '',
            ''
        );

        $this->templateRepository->save($certificateTemplate);

        $this->overwriteBackgroundImageThumbnail($certificateTemplate);
    }

    /**
     * @param $previousTemplate
     */
    private function overwriteBackgroundImageThumbnail(\ilCertificateTemplate $previousTemplate)
    {
        $relativePath = $previousTemplate->getBackgroundImagePath();

        if (null === $relativePath || '' === $relativePath) {
            $relativePath = '/certificates/default/background.jpg';
        }

        $pathInfo = pathinfo($relativePath);

        $newFilePath = $pathInfo['dirname'] . '/background.jpg.thumb.jpg';

        $this->utilHelper->convertImage(
            $this->rootDirectory . $relativePath,
            $this->rootDirectory . $newFilePath,
            'JPEG',
            "100"
        );
    }
}
