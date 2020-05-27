<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilGenerator extends \ilPdfGenerator
{
    /**
     * @var \ilUserCertificateRepository
     */
    private $certificateRepository;

    /**
     * @var \ilLogger
     */
    private $logger;

    /**
     * @var \ilLanguage
     */
    private $lng;

    /**
     * @var \ilCertificateRpcClientFactoryHelper|null
     */
    private $rpcHelper;

    /**
     * @var \ilCertificateScormPdfFilename|null
     */
    private $scormPdfFilename;

    /**
     * @var \ilCertificatePdfFileNameFactory|null
     */
    private $pdfFilenameFactory;

    /**
     * @param \ilUserCertificateRepository $userCertificateRepository
     * @param \ilLogger $logger
     * @param \ilLanguage $lng
     * @param \ilCertificateRpcClientFactoryHelper|null $rpcHelper
     * @param \ilCertificatePdfFileNameFactory|null $pdfFileNameFactory
     */
    public function __construct(
        \ilUserCertificateRepository $userCertificateRepository,
        \ilLogger $logger,
        \ilLanguage $lng,
        \ilCertificateRpcClientFactoryHelper $rpcHelper = null,
        \ilCertificatePdfFileNameFactory $pdfFileNameFactory = null
    ) {
        $this->certificateRepository = $userCertificateRepository;
        $this->logger = $logger;
        $this->lng = $lng;

        if (null === $rpcHelper) {
            $rpcHelper = new \ilCertificateRpcClientFactoryHelper();
        }
        $this->rpcHelper = $rpcHelper;

        if (null === $pdfFileNameFactory) {
            $pdfFileNameFactory = new \ilCertificatePdfFileNameFactory($this->lng);
        }
        $this->pdfFilenameFactory = $pdfFileNameFactory;
    }

    /**
     * @param $userCertificateId
     * @return mixed
     * @throws \ilException
     */
    public function generate(int $userCertificateId)
    {
        $certificate = $this->certificateRepository->fetchCertificate($userCertificateId);

        return $this->createPDFScalar($certificate);
    }

    /**
     * @param $userId
     * @param $objId
     * @return mixed
     * @throws \ilException
     */
    public function generateCurrentActiveCertificate(int $userId, int $objId) : string
    {
        $certificate = $this->certificateRepository->fetchActiveCertificate($userId, $objId);

        return $this->createPDFScalar($certificate);
    }

    public function generateFileName(int $userId, int $objId) : string
    {
        /** @var \ilObjUser $user */
        $user = \ilObjectFactory::getInstanceByObjId($userId);
        return $user->getFirstname() . "_" . $user->getLastname() . "_Certificate.pdf";
    }

    /**
     * @param $certificate
     * @return mixed
     */
    private function createPDFScalar(\ilUserCertificate $certificate) : string
    {
        $certificateContent = $certificate->getCertificateContent();
        $pdf_base64 = $this->rpcHelper->ilFO2PDF('RPCTransformationHandler', $certificateContent);

        return $pdf_base64->scalar;
    }
}
