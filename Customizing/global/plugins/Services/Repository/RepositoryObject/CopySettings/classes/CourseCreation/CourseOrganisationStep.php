<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace CaT\Plugins\CopySettings\CourseCreation;

use ILIAS\TMS\CourseCreation\Step;
use ILIAS\TMS\CourseCreation\RequestBuilder;
use ILIAS\TMS\CourseCreation\ChildAssistant;
use \CaT\Ente\ILIAS\Entity;

use CaT\Plugins\Venues;
use CaT\Plugins\TrainingProvider;

/**
 * Step to configure organisationals infos for the course
 */
class CourseOrganisationStep extends \CourseCreationStep
{
    use ChildAssistant;

    const INPUT_VENUE_SOURCE = "venue_source";
    const INPUT_VENUE_TEXT = "venue_text";
    const INPUT_VENUE_LIST = "venue_list";
    const INPUT_VENUE_LIST_ADDITIONAL = "venue_additional";

    const INPUT_PROVIDER_SOURCE = "provider_source";
    const INPUT_PROVIDER_TEXT = "provider_text";
    const INPUT_PROVIDER_LIST = "provider_list";

    const INPUT_IMPORTANT = "important";

    /**
     * @var	Entity
     */
    protected $entity;

    /**
     * @var	RequestBuilder|null
     */
    protected $request_builder;

    /**
     * @var \Closure
     */
    protected $txt;

    public function __construct(Entity $entity, \Closure $txt, $owner)
    {
        $this->entity = $entity;
        $this->txt = $txt;
        $this->owner = $owner;
    }

    // from Ente\Component

    /**
     * @inheritdocs
     */
    public function entity()
    {
        return $this->entity;
    }

    // from TMS\Wizard\Step

    /**
     * @inheritdocs
     */
    public function getLabel()
    {
        return $this->txt("course_organisation");
    }

    /**
     * @inheritdocs
     */
    public function getDescription()
    {
        return $this->txt("course_organisation_desc");
    }

    /**
     * @inheritdocs
     */
    public function appendToStepForm(\ilPropertyFormGUI $form)
    {
        require_once("Services/Form/classes/class.ilNonEditableValueGUI.php");
        require_once("Services/Form/classes/class.ilFormSectionHeaderGUI.php");

        $this->addVenueAndProvider($form);
        $this->addOrganisational($form);
    }

    /**
     * @inheritdocs
     */
    public function addDataToForm(\ilPropertyFormGUI $form, $data)
    {
        if (count($data) > 0) {
            $form->setValuesByArray($data);
        }
    }

    /**
     * @inheritdocs
     */
    public function appendToOverviewForm(\ilPropertyFormGUI $form, $data)
    {
        $lng = $this->getIlLanguage();
        $lng->loadLanguageModule("crs");
        $lng->loadLanguageModule("sess");

        if (\ilPluginAdmin::isPluginActive('venues')) {
            $vplug = \ilPluginAdmin::getPluginObjectById('venues');
            $plugin_txt = $vplug->txtClosure();

            if ($data[self::INPUT_VENUE_SOURCE] == \ilCourseConstants::VENUE_FROM_TEXT) {
                $text = nl2br($data[self::INPUT_VENUE_TEXT]);
                $add_text = "-";
            } else {
                $vactions = $vplug->getActions();
                $text = nl2br(
                    $this->getVenueTextForOverview((int) $data[self::INPUT_VENUE_LIST], $vactions)
                );
                $add_text = $data[self::INPUT_VENUE_LIST_ADDITIONAL];
                if (trim($add_text) == "") {
                    $add_text = "-";
                }
            }

            $item = new \ilNonEditableValueGUI($plugin_txt("crs_venue_source"), "", true);
            $item->setValue($text);
            $form->addItem($item);

            $item = new \ilNonEditableValueGUI($plugin_txt("crs_venue_list_additional"), "", true);
            $item->setValue($add_text);
            $form->addItem($item);
        }

        if (\ilPluginAdmin::isPluginActive('trainingprovider')) {
            $pplug = \ilPluginAdmin::getPluginObjectById('trainingprovider');
            $plugin_txt = $pplug->txtClosure();


            if ($data[self::INPUT_PROVIDER_SOURCE] == \ilCourseConstants::PROVIDER_FROM_TEXT) {
                $text = nl2br($data[self::INPUT_PROVIDER_TEXT]);
            } else {
                $val = array();
                $pactions = $pplug->getActions();
                $text = $this->getProviderTextForOverview((int) $data[self::INPUT_PROVIDER_LIST], $pactions);
            }

            $item = new \ilNonEditableValueGUI($plugin_txt("crs_provider_source"), "", true);
            $item->setValue($text);
            $form->addItem($item);
        }

        $text = trim($data[self::INPUT_IMPORTANT]);
        if (is_null($text) || $text == "") {
            $text = "-";
        } else {
            $text = nl2br($text);
        }
        $item = new \ilNonEditableValueGUI($lng->txt("crs_important_info"), "", true);
        $item->setValue($text);
        $form->addItem($item);
    }

    /**
     * @inheritdocs
     */
    public function processStep($data)
    {
        $crs = $this->entity()->object();
        $this->request_builder->addConfigurationFor(
            $crs,
            ["important_information" => $data["important"]]
        );

        if ((int) $data["venue_source"] === 0) {
            $this->request_builder->addConfigurationFor(
                $crs,
                ["venue_free_text" => $data["venue_text"]]
            );
        } else {
            $this->request_builder->addConfigurationFor(
                $crs,
                [
                    "venue_fixed" => [
                        "venue_list" => $data["venue_list"],
                        "venue_additional" => $data["venue_additional"]
                    ]
                ]
            );
        }
        if ((int) $data["provider_source"] === 0) {
            $this->request_builder->addConfigurationFor(
                $crs,
                ["provider_free_text" => $data["provider_text"]]
            );
        } else {
            $this->request_builder->addConfigurationFor(
                $crs,
                ["provider_fixed" => $data["provider_list"]]
            );
        }
    }

    /**
     * @inheritdocs
     */
    public function getData(\ilPropertyFormGUI $form)
    {
        $data = array();

        $data[self::INPUT_VENUE_SOURCE] = $form->getInput(self::INPUT_VENUE_SOURCE);
        $data[self::INPUT_VENUE_TEXT] = $form->getInput(self::INPUT_VENUE_TEXT);
        $data[self::INPUT_VENUE_LIST] = $form->getInput(self::INPUT_VENUE_LIST);
        $data[self::INPUT_VENUE_LIST_ADDITIONAL] = $form->getInput(self::INPUT_VENUE_LIST_ADDITIONAL);

        $data[self::INPUT_PROVIDER_SOURCE] = $form->getInput(self::INPUT_PROVIDER_SOURCE);
        $data[self::INPUT_PROVIDER_TEXT] = $form->getInput(self::INPUT_PROVIDER_TEXT);
        $data[self::INPUT_PROVIDER_LIST] = $form->getInput(self::INPUT_PROVIDER_LIST);

        $data[self::INPUT_IMPORTANT] = $form->getInput(self::INPUT_IMPORTANT);

        return $data;
    }

    // from TMS\CourseCreation\Step

    /**
     * @inheritdocs
     */
    public function getPriority()
    {
        return 400;
    }

    /**
     * @inheritdocs
     */
    public function isApplicable()
    {
        return true;
    }

    /**
     * @inheritdocs
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @inheritdocs
     */
    public function setRequestBuilder(RequestBuilder $request_builder)
    {
        $this->request_builder = $request_builder;
    }

    /**
     * Get the ref id of entity object
     *
     * @return int
     */
    protected function getEntityRefId()
    {
        return $this->entity()->object()->getRefId();
    }

    /**
     * Get the  entity object
     *
     * @return int
     */
    protected function getEntityObject()
    {
        return $this->entity()->object();
    }

    /**
     * Adds infos of course classification to form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addVenueAndProvider(\ilPropertyFormGUI $form)
    {
        $crs = $this->getEntityObject();
        $lng = $this->getIlLanguage();
        $lng->loadLanguageModule("crs");
        $lng->loadLanguageModule("sess");

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("sec_venue_and_provider"));
        $form->addItem($sec);

        if (\ilPluginAdmin::isPluginActive('venues')) {
            $vplug = \ilPluginAdmin::getPluginObjectById('venues');
            $vactions = $vplug->getActions();
            $plugin_txt = $vplug->txtClosure();

            $venue_input = $this->getVenueInput($crs, $vactions, $plugin_txt);
            $form->addItem($venue_input);
        }

        if (\ilPluginAdmin::isPluginActive('trainingprovider')) {
            $pplug = \ilPluginAdmin::getPluginObjectById('trainingprovider');
            $pactions = $pplug->getActions();
            $plugin_txt = $pplug->txtClosure();

            $provider_input = $this->getProviderInput($crs, $pactions, $plugin_txt);
            $form->addItem($provider_input);
        }
    }

    /**
     * Get the input system for venues
     */
    protected function getVenueInput(\ilObjCourse $crs, $vactions, \Closure $plugin_txt)
    {
        $settings = $this->owner->getExtendedSettings();
        $edit_venue = $settings->getEditVenue();

        //build options for select-input
        $venues = $vactions->getAllVenues('name', 'ASC', null);
        $voptions = array(null => $plugin_txt("please_select"));
        foreach ($venues as $v) {
            $voptions[$v->getGeneral()->getId()] = $v->getGeneral()->getName()
                . ', '
                . $v->getAddress()->getCity()
            ;
        }
        $venue_opts = new \ilRadioGroupInputGUI(
            $plugin_txt('crs_venue_source'),
            self::INPUT_VENUE_SOURCE
        );
        $venue_opts->setRequired(true);
        $venue_opts->setDisabled(!$edit_venue);

        //create inputs
        $venue_opt_text = new \ilRadioOption(
            $plugin_txt('crs_venue_source_text'),
            \ilCourseConstants::VENUE_FROM_TEXT
        );
        $venue_opt_text->setDisabled(!$edit_venue);

        $venue_opt_text_inp = new \ilTextAreaInputGUI(
            $plugin_txt('crs_venue_text'),
            self::INPUT_VENUE_TEXT
        );
        $venue_opt_text_inp->setRows(6);
        $venue_opt_text_inp->setCols(80);
        $venue_opt_text_inp->setRequired(true);
        $venue_opt_text_inp->setDisabled(!$edit_venue);
        $venue_opt_text->addSubItem($venue_opt_text_inp);

        $venue_opt_list = new \ilRadioOption(
            $plugin_txt('crs_venue_source_list'),
            \ilCourseConstants::VENUE_FROM_LIST
        );
        $venue_opt_list->setDisabled(!$edit_venue);

        $venue_opt_list_inp = new \ilSelectInputGUI(
            $plugin_txt('crs_venue_list'),
            self::INPUT_VENUE_LIST
        );
        $venue_opt_list_inp->setRequired(true);
        $venue_opt_list_inp->setDisabled(!$edit_venue);
        $venue_opt_list_inp->setOptions($voptions);

        $venue_opt_list_additional = new \ilTextInputGUI(
            $plugin_txt('crs_venue_list_additional'),
            self::INPUT_VENUE_LIST_ADDITIONAL
        );
        $venue_opt_list_additional->setDisabled(!$edit_venue);

        $venue_opt_list->addSubItem($venue_opt_list_inp);
        $venue_opt_list->addSubItem($venue_opt_list_additional);

        //set values
        $vassignment_type = \ilCourseConstants::VENUE_FROM_LIST; //default
        $vassignment = $vactions->getAssignment((int) $crs->getId());

        if ($vassignment) {
            if ($vassignment->isCustomAssignment()) {
                $vassignment_type = \ilCourseConstants::VENUE_FROM_TEXT;
                $venue_opt_text_inp->setValue($vassignment->getVenueText());
            }

            if ($vassignment->isListAssignment()) {
                $vassignment_type = \ilCourseConstants::VENUE_FROM_LIST;
                $venue_opt_list_inp->setValue($vassignment->getVenueId());
                $venue_opt_list_additional->setValue($vassignment->getAdditionalInfo());
            }
        }
        $venue_opts->setValue($vassignment_type);

        //add options to form
        $venue_opts->addOption($venue_opt_text);
        $venue_opts->addOption($venue_opt_list);

        return $venue_opts;
    }

    /**
     * Get the venue address for overview form
     *
     * @param int 	$venue_id
     * @param Venues\ilActions 	$vactions
     *
     * @return string
     */
    protected function getVenueTextForOverview($venue_id, Venues\ilActions $vactions)
    {
        assert('is_int($venue_id)');

        $venue = $vactions->getVenue($venue_id);
        $city = $venue->getAddress()->getCity();
        $address = $venue->getAddress()->getAddress1();
        $name = $venue->getGeneral()->getName();
        $postcode = $venue->getAddress()->getPostcode();

        if ($name != "") {
            $val[] = $name;
        }

        if ($address != "") {
            $val[] = $address;
        }

        if ($postcode != "" || $city != "") {
            $val[] = $postcode . " " . $city;
        }

        return join("<br />", $val);
    }

    /**
     * Get the input system for venues
     *
     * @param
     *
     * @return \ilRadioGroupInputGUI
     */
    protected function getProviderInput(\ilObjCourse $crs, $pactions, \Closure $plugin_txt)
    {
        $settings = $this->owner->getExtendedSettings();
        $edit_provider = $settings->getEditProvider();

        //build options for select-input
        $provider = $pactions->getAllProviders('name', 'ASC');
        $poptions = array(null => $plugin_txt("please_select"));
        foreach ($provider as $p) {
            $poptions[$p->getId()] = $p->getName() . ', ' . $p->getCity();
        }
        $provider_opts = new \ilRadioGroupInputGUI(
            $plugin_txt('crs_provider_source'),
            self::INPUT_PROVIDER_SOURCE
        );
        $provider_opts->setRequired(true);
        $provider_opts->setDisabled(!$edit_provider);

        //create inputs
        $provider_opt_text = new \ilRadioOption(
            $plugin_txt('crs_provider_source_text'),
            \ilCourseConstants::PROVIDER_FROM_TEXT
        );
        $provider_opt_text->setDisabled(!$edit_provider);

        $provider_opt_text_inp = new \ilTextAreaInputGUI(
            $plugin_txt('crs_provider_text'),
            self::INPUT_PROVIDER_TEXT
        );
        $provider_opt_text_inp->setRows(6);
        $provider_opt_text_inp->setCols(80);
        $provider_opt_text_inp->setRequired(true);
        $provider_opt_text_inp->setDisabled(!$edit_provider);
        $provider_opt_text->addSubItem($provider_opt_text_inp);

        $provider_opt_list = new \ilRadioOption(
            $plugin_txt('crs_provider_source_list'),
            \ilCourseConstants::PROVIDER_FROM_LIST
        );
        $provider_opt_list->setDisabled(!$edit_provider);

        $provider_opt_list_inp = new \ilSelectInputGUI(
            $plugin_txt('crs_provider_list'),
            self::INPUT_PROVIDER_LIST
        );
        $provider_opt_list_inp->setRequired(true);
        $provider_opt_list_inp->setOptions($poptions);
        $provider_opt_list_inp->setDisabled(!$edit_provider);
        $provider_opt_list->addSubItem($provider_opt_list_inp);

        //set values
        $passignment_type = \ilCourseConstants::PROVIDER_FROM_LIST; //default
        $passignment = $pactions->getAssignment((int) $crs->getId());

        if ($passignment) {
            if ($passignment->isCustomAssignment()) {
                $passignment_type = \ilCourseConstants::PROVIDER_FROM_TEXT;
                $provider_opt_text_inp->setValue($passignment->getProviderText());
            }

            if ($passignment->isListAssignment()) {
                $passignment_type = \ilCourseConstants::PROVIDER_FROM_LIST;
                $provider_opt_list_inp->setValue($passignment->getProviderId());
            }
        }
        $provider_opts->setValue($passignment_type);

        //add options to form
        $provider_opts->addOption($provider_opt_text);
        $provider_opts->addOption($provider_opt_list);

        return $provider_opts;
    }

    /**
     * Get the provider address for overview form
     *
     * @param int 	$provider_id
     * @param TrainingProvider\ilActions 	$pactions
     *
     * @return string
     */
    protected function getProviderTextForOverview($provider_id, TrainingProvider\ilActions $pactions)
    {
        assert('is_int($provider_id)');

        $provider = $pactions->getProvider($provider_id);
        $city = $provider->getCity();
        $address = $provider->getAddress1();
        $name = $provider->getName();
        $postcode = $provider->getPostcode();

        if ($name != "") {
            $val[] = $name;
        }

        if ($address != "") {
            $val[] = $address;
        }

        if ($postcode != "" || $city != "") {
            $val[] = $postcode . " " . $city;
        }

        return join("<br />", $val);
    }

    /**
     * Adds infos of all sessions form
     *
     * @param \ilPropertyFormGUI
     *
     * @return void
     */
    protected function addOrganisational(\ilPropertyFormGUI $form)
    {
        $crs = $this->getEntityObject();
        $lng = $this->getIlLanguage();

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt("sec_organisational"));
        $form->addItem($sec);

        $area = new \ilTextAreaInputGUI(
            $lng->txt('crs_important_info'),
            self::INPUT_IMPORTANT
        );
        $area->setValue($crs->getImportantInformation());
        $area->setRows(6);
        $area->setCols(80);
        $form->addItem($area);

        // TODO: File Upload/Download. Look Concept page 17
    }

    /**
     * Get CourseClassification where user as permission to copy
     *
     * @return ilObjCourseClassification | null
     */
    protected function getSessions()
    {
        $sesss = $this->getAllChildrenOfByType($this->getEntityRefId(), "sess");
        $sesss = array_filter($sesss, function ($sess) {
            $sess_ref_id = $sess->getRefId();
            return $this->checkAccess(["visible", "read", "copy"], $sess_ref_id);
        });

        if (count($sesss) == 0) {
            return array();
        }

        return $sesss;
    }

    /**
     * Get day times as string
     *
     * @param string[]
     *
     * @return string
     */
    protected function getTimesAsString(array $times)
    {
        return str_pad(
            $times["hh"],
            2,
            "0",
            STR_PAD_LEFT
        )
            . ":"
            . str_pad($times["mm"], 2, "0", STR_PAD_LEFT);
    }

    /**
     * Get the ILIAS dictionary
     *
     * @return \ArrayAccess | array
     */
    protected function getDIC()
    {
        return $GLOBALS["DIC"];
    }

    /**
     * Get the ilLanguage
     *
     * @return \ilLanguage
     */
    protected function getIlLanguage()
    {
        return $this->getDIC()->language();
    }

    /**
     * i18n
     *
     * @param	string	$id
     * @return	string	$text
     */
    protected function txt($id)
    {
        assert('is_string($id)');
        return call_user_func($this->txt, $id);
    }
}
