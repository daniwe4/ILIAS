<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\OverviewCertificate;

use CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilCertificateHandling
{
    const TYPE = "xebr";

    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilCertificateValueReplacement
     */
    protected $certificate_value_replacement;

    /**
     * @var Certificate\ilPlaceholderValues
     */
    protected $placeholder_values;

    /**
     * @var \ilUserCertificateRepository
     */
    protected $user_certificate_repository;

    /**
     * @var \ilCertificateTemplateRepository
     */
    protected $template_repository;

    /**
     * @var \ilPdfGenerator
     */
    protected $pdf_generator;

    /**
     * @var \ilLogger
     */
    protected $certificate_logger;

    /**
     * @var \ilCertificateUtilHelper
     */
    protected $certificate_util_helper;

    public function __construct(
        \ilObjUser $user,
        \ilCertificateValueReplacement $certificate_value_replacement,
        Certificate\ilPlaceholderValues $placeholder_values,
        \ilUserCertificateRepository $user_certificate_repository,
        \ilCertificateTemplateRepository $template_repository,
        \ilPdfGenerator $pdf_generator,
        \ilLogger $certificate_logger,
        \ilCertificateUtilHelper $certificate_util_helper
    ) {
        $this->user = $user;
        $this->certificate_value_replacement = $certificate_value_replacement;
        $this->placeholder_values = $placeholder_values;
        $this->user_certificate_repository = $user_certificate_repository;
        $this->template_repository = $template_repository;
        $this->pdf_generator = $pdf_generator;
        $this->certificate_logger = $certificate_logger;
        $this->certificate_util_helper = $certificate_util_helper;
    }

    /**
     * @throws \ilException
     */
    public function getCurrentCertificate(int $usr_id, int $schedule_id)
    {
        return $this->user_certificate_repository->fetchActiveCertificate($usr_id, $schedule_id);
    }

    /**
     * @throws \ilException
     */
    public function createUserCertificate(int $usr_id, int $schedule_id)
    {
        $template = $this->template_repository->fetchCurrentlyActiveCertificate($schedule_id);
        if ($template->isCurrentlyActive()) {
            $this->processEntry($schedule_id, $usr_id, $template);
        }
    }

    public function deliverCertificate(int $usr_id, int $schedule_id, string $error_message)
    {
        $pdfAction = new \ilCertificatePdfAction(
            $this->certificate_logger,
            $this->pdf_generator,
            $this->certificate_util_helper,
            $error_message
        );

        $pdfAction->downloadPdf($usr_id, $schedule_id);
    }

    private function processEntry(int $objectId, int $userId, \ilCertificateTemplate $template)
    {
        $certificate_content = $template->getCertificateContent();
        $placeholder_values = $this->placeholder_values->getPlaceholderValues($userId, $objectId);

        $certificate_content = $this->certificate_value_replacement->replace(
            $placeholder_values,
            $certificate_content,
            CLIENT_WEB_DIR . $template->getBackgroundImagePath()
        );

        $userCertificate = new \ilUserCertificate(
            $template->getId(),
            $objectId,
            self::TYPE,
            $userId,
            $this->user->getFullname(),
            (int) time(),
            $certificate_content,
            json_encode($placeholder_values),
            null,
            $template->getVersion(),
            ILIAS_VERSION_NUMERIC,
            true,
            $template->getBackgroundImagePath(),
            (string) $template->getThumbnailImagePath()
        );

        $this->user_certificate_repository->save($userCertificate);
    }
}
