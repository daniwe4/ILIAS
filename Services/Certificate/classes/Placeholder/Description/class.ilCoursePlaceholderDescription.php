<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescription implements ilCertificatePlaceholderDescription
{
    /**
     * @var ilDefaultPlaceholderDescription
     */
    private $defaultPlaceHolderDescriptionObject;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var array
     */
    private $placeholder;

    /**
     * // cat-tms-patch start #3886
     * @param ilTMSCertificatePlaceholderDescription | null $tms_placeholder_description
     * // cat-tms-patch end
     * @param ilDefaultPlaceholderDescription|null           $defaultPlaceholderDescriptionObject
     * @param ilLanguage|null                                $language
     * @param ilUserDefinedFieldsPlaceholderDescription|null $userDefinedFieldPlaceHolderDescriptionObject
     */
    public function __construct(
        // cat-tms-patch start #3886
        ilTMSCertificatePlaceholderDescription $tms_placeholder_description = null,
        // cat-tms-patch end
        ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ilLanguage $language = null,
        ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
    ) {
        global $DIC;
        // cat-tms-patch start #3886
        if (is_null($tms_placeholder_description)) {
            $tms_placeholder_description = new ilTMSCertificatePlaceholderDescription($DIC["lng"]);
        }
        // cat-tms-patch end

        if (null === $language) {
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

        if (null === $defaultPlaceholderDescriptionObject) {
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language, $userDefinedFieldPlaceHolderDescriptionObject);
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        $this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();
        $this->placeholder['COURSE_TITLE'] = $this->language->txt('crs_title');
        $this->placeholder['DATE_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_date_completed'));
        $this->placeholder['DATETIME_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_datetime_completed'));

        // cat-tms-patch start #3886
        if (is_null($tms_placeholder_description)) {
            $this->placeholder = array_merge($this->placeholder, $tms_placeholder_description->getTMSPlaceholder());
        }
        // cat-tms-patch end
    }

    /**
     * This methods MUST return an array containing an array with
     * the the description as array value.
     * @param null $template
     * @return mixed - [PLACEHOLDER] => 'description'
     */
    public function createPlaceholderHtmlDescription(ilTemplate $template = null) : string
    {
        if (null === $template) {
            $template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
        }

        $template->setVariable("PLACEHOLDER_INTRODUCTION", $this->language->txt('certificate_ph_introduction'));

        $template->setCurrentBlock("items");
        foreach ($this->placeholder as $id => $caption) {
            $template->setVariable("ID", $id);
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return mixed - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }
}
