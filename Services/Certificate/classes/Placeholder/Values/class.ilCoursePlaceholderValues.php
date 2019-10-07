<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValues implements ilCertificatePlaceholderValues
{
    /**
     * @var ilDefaultPlaceholderValues
     */
    private $defaultPlaceHolderValuesObject;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var ilCertificateObjectHelper|null
     */
    private $objectHelper;

    /**
     * @var ilCertificateParticipantsHelper|null
     */
    private $participantsHelper;

    /**
     * @var ilCertificateUtilHelper
     */
    private $ilUtilHelper;

    /**
     * @var ilCertificateDateHelper|null
     */
    private $dateHelper;

    // cat-tms-patch start #3886
    /**
    * @var ilTMSCertificatePlaceholderValues
    */
    protected $tms_placeholder_values;
    // cat-tms-patch end

    /**
     * // cat-tms-patch start #3886
     * @param ilTMSCertificatePlaceholderValues | null  $tms_placeholder_values
     * // cat-tms-patch end
     * @param ilDefaultPlaceholderValues           $defaultPlaceholderValues
     * @param ilLanguage|null                      $language
     * @param ilCertificateObjectHelper|null       $objectHelper
     * @param ilCertificateParticipantsHelper|null $participantsHelper
     * @param ilCertificateUtilHelper              $ilUtilHelper
     * @param ilCertificateDateHelper|null         $ilDateHelper
     */
    public function __construct(
        // cat-tms-patch start #3886
        ilTMSCertificatePlaceholderValues $tms_placeholder_values = null,
        // cat-tms-patch end
        ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
        ilLanguage $language = null,
        ilCertificateObjectHelper $objectHelper = null,
        ilCertificateParticipantsHelper $participantsHelper = null,
        ilCertificateUtilHelper $ilUtilHelper = null,
        ilCertificateDateHelper $dateHelper = null
    ) {
        // cat-tms-patch start #3886
        global $DIC;
        if (is_null($tms_placeholder_values)) {
            $tms_placeholder_values = new ilTMSCertificatePlaceholderValues($DIC["lng"], $DIC["tree"]);
        }
        $this->tms_placeholder_values = $tms_placeholder_values;
        // cat-tms-patch end

        if (null === $language) {
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

        if (null === $defaultPlaceholderValues) {
            $defaultPlaceholderValues = new ilDefaultPlaceholderValues();
        }

        if (null === $objectHelper) {
            $objectHelper = new ilCertificateObjectHelper();
        }
        $this->objectHelper = $objectHelper;

        if (null === $participantsHelper) {
            $participantsHelper = new ilCertificateParticipantsHelper();
        }
        $this->participantsHelper = $participantsHelper;

        if (null === $ilUtilHelper) {
            $ilUtilHelper = new ilCertificateUtilHelper();
        }
        $this->ilUtilHelper = $ilUtilHelper;

        $this->defaultPlaceHolderValuesObject = $defaultPlaceholderValues;

        if (null === $dateHelper) {
            $dateHelper = new ilCertificateDateHelper();
        }
        $this->dateHelper = $dateHelper;

        $this->defaultPlaceHolderValuesObject = $defaultPlaceholderValues;
    }

    /**
     * This method MUST return an array that contains the
     * actual data for the given user of the given object.
     * ilInvalidCertificateException MUST be thrown if the
     * data could not be determined or the user did NOT
     * achieve the certificate.
     * @param $userId
     * @param $objId
     * @return mixed - [PLACEHOLDER] => 'actual value'
     * @throws ilException
     */
    public function getPlaceholderValues(int $userId, int $objId) : array
    {
        $courseObject = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

        $placeholders['COURSE_TITLE'] = $this->ilUtilHelper->prepareFormOutput($courseObject->getTitle());
        $completionDate = $this->participantsHelper->getDateTimeOfPassed($objId, $userId);

        if ($completionDate !== false &&
            $completionDate !== null &&
            $completionDate !== ''
        ) {
            $placeholders['DATE_COMPLETED'] = $this->dateHelper->formatDate($completionDate);
            $placeholders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
        }

        $placeholders['COURSE_TITLE'] = $this->ilUtilHelper->prepareFormOutput($courseObject->getTitle());

        // cat-tms-patch start #3886
        $placeholders = array_merge(
            $placeholders,
            $this->tms_placeholder_values->getTMSPlaceholderValues($courseObject, $userId)
        );
        // cat-tms-patch end

        return $placeholders;
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     * @param int $userId
     * @param int $objId
     * @return mixed
     */
    public function getPlaceholderValuesForPreview(int $userId, int $objId)
    {
        $placeholders = $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

        $object = $this->objectHelper->getInstanceByObjId($objId);

        $placeholders['COURSE_TITLE'] = ilUtil::prepareFormOutput($object->getTitle());
        // cat-tms-patch start #3886
        $placeholders = array_merge(
            $placeholders,
            $this->tms_placeholder_values->getTMSVariablesForPreview()
        );
        // cat-tms-patch end

        return $placeholders;
    }
}
