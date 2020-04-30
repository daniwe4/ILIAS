<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");


use \CaT\Plugins\Venues;

/**
 * Plugin base class. Keeps all information the plugin needs
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilVenuesPlugin extends ilCronHookPlugin
{
    use Venues\DI;

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Venues\Venues\DB
     */
    protected $venues_db;

    /**
     * @var Venues\Tags\DB
     */
    protected $tags_db;

    /**
     * @var Venues\VenueAssignement\DB
     */
    protected $assign_db;

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "Venues";
    }

    /**
     * Get an array with 1 to n numbers of cronjob objects
     *
     * @return ilJob[]
     */
    public function getCronJobInstances()
    {
    }

    /**
     * Get a single cronjob object
     *
     * @return ilJob
     */
    public function getCronJobInstance($a_job_id)
    {
    }

    /**
     * Get action of plugin
     *
     * @return ilActions
     */
    public function getActions()
    {
        if ($this->actions === null) {
            global $DIC;
            $db = $DIC->database();

            $this->actions = new Venues\ilActions(
                $this,
                $this->getVenuesDB($db),
                $this->getGeneralDB($db),
                $this->getAddressDB($db),
                $this->getConditionsDB($db),
                $this->getContactDB($db),
                $this->getRatingDB($db),
                $this->getCapacityDB($db),
                $this->getServiceDB($db),
                $this->getCostsDB($db),
                $this->getVenueTagsDB($db),
                $this->getAssingmentDB($db),
                $DIC["ilAppEventHandler"],
                $this->getSearchTagsDB($db)
            );
        }

        return $this->actions;
    }

    /**
     * Get DB interface for venues
     *
     * @param $db
     *
     * @return Venues\Venues\DB
     */
    protected function getVenuesDB($db)
    {
        return $this->getDIC()["venues.db"];
    }

    /**
     * Get DB interface for general configuration
     *
     * @param $db
     *
     * @return Venues\Venues\General\DB
     */
    protected function getGeneralDB($db)
    {
        return $this->getDIC()["venues.general.db"];
    }

    /**
     * Get DB interface for address configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Address\DB
     */
    protected function getAddressDB($db)
    {
        return $this->getDIC()["venues.address.db"];
    }

    /**
     * Get DB interface for conditions configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Conditions\DB
     */
    protected function getConditionsDB($db)
    {
        return $this->getDIC()["venues.conditions.db"];
    }

    /**
     * Get DB interface for contact configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Contact\DB
     */
    protected function getContactDB($db)
    {
        return $this->getDIC()["venues.contact.db"];
    }

    /**
     * Get DB interface for rating configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Rating\DB
     */
    protected function getRatingDB($db)
    {
        return $this->getDIC()["venues.rating.db"];
    }

    /**
     * Get DB interface for capacity configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Capacity\DB
     */
    protected function getCapacityDB($db)
    {
        return $this->getDIC()["venues.capacity.db"];
    }

    /**
     * Get DB interface for service configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Service\DB
     */
    protected function getServiceDB($db)
    {
        return $this->getDIC()["venues.service.db"];
    }

    /**
     * Get DB interface for costs configuration
     *
     * @param $db
     *
     * @return Venues\Venues\Coszs\DB
     */
    protected function getCostsDB($db)
    {
        return $this->getDIC()["venues.costs.db"];
    }

    /**
     * Get DB interface for tags
     *
     * @param $db
     *
     * @return Venues\Tags\Venue\DB
     */
    protected function getVenueTagsDB($db)
    {
        return $this->getDIC()["config.tags.venue.db"];
    }

    /**
     * Get DB interface for tags
     *
     * @param $db
     *
     * @return Venues\Tags\Search\DB
     */
    protected function getSearchTagsDB($db)
    {
        return $this->getDIC()["config.tags.search.db"];
    }

    /**
     * Get DB interface for venues/course assignment
     *
     * @param $db
     *
     * @return Venues\VenueAssignement\DB
     */
    protected function getAssingmentDB($db)
    {
        return $this->getDIC()["venues.assignments.db"];
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Get form helper for
     *
     * @param string 	$config_section
     *
     * @return Venues\Veneus\ConfigFormHelper
     */
    public function getFormHelperFor($config_section)
    {
        $form_helper = "\\CaT\\Plugins\\Venues\\Venues\\" . $config_section . "\\FormHelper";
        return new $form_helper($this->getActions(), $this->txtClosure());
    }

    /**
     * Get information about selected venue
     *
     * @param int 	$crs_id
     *
     * @return string[]
     */
    public function getVenueInfos(int $crs_id) : array
    {
        $vactions = $this->getActions();
        $vassignment = $vactions->getAssignment((int) $crs_id);

        $venue_id = -1;
        $city = "";
        $address = "";
        $name = "";
        $postcode = "";
        $custom_assignment = false;
        $addtitional_info = '';
        $tags = [];

        if ($vassignment) {
            if ($vassignment->isCustomAssignment()) {
                $name = $vassignment->getVenueText();
                $custom_assignment = true;
            }

            if ($vassignment->isListAssignment()) {
                $venue_id = $vassignment->getVenueId();
                $venue = $vactions->getVenue($venue_id);
                $city = $venue->getAddress()->getCity();
                $address = $venue->getAddress()->getAddress1();
                $name = $venue->getGeneral()->getName();
                $postcode = $venue->getAddress()->getPostcode();
                $addtitional_info = $vassignment->getAdditionalInfo();
                $tags = $venue->getGeneral()->getSearchTags();
            }
        }

        return array($venue_id, $city, $address, $name, $postcode, $custom_assignment, $addtitional_info, $tags);
    }

    /**
     * @return string[]
     */
    public function getUsedSearchTags() : array
    {
        return $this->getDIC()["config.tags.search.db"]->getUsedSearchTagsRaw();
    }

    protected function getDIC()
    {
        if (is_null($this->dic)) {
            global $DIC;
            $this->dic = $this->getPluginDIC($this, $DIC);
        }

        return $this->dic;
    }
}
