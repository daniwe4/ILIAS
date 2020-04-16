<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Course;

use CaT\Plugins\MaterialList\RPC;

require_once("./Services/Component/classes/class.ilPluginAdmin.php");

/**
 * Procedure to get the event period
 */
class ilGetVenue extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "venue";

    /**
     * Return the title of venue
     *
     * @return string
     */
    public function run()
    {
        return new RPC\FunctionResult($this->txt(self::COLUMN_TITLE), $this->getVenue((int) $this->crs->getId()));
    }

    /**
     * Add venue for crs if plugin is active
     *
     * @param int 		$crs_id
     * @return string[]
     */
    protected function getVenue($crs_id)
    {
        $ret = array();
        if ($this->isPluginActive('venues')) {
            $vplugin = \ilPluginAdmin::getPluginObjectById('venues');
            $vactions = $vplugin->getActions();
            $vassignment = $vactions->getAssignment($crs_id);

            if ($vassignment === false) {
                return;
            }

            if ($vassignment->isCustomAssignment()) {
                $ret[] = $vassignment->getVenueText();
            } elseif ($vassignment->isListAssignment()) {
                $venue = $vactions->getVenue($vassignment->getVenueId());
                $general = $venue->getGeneral();
                $address = $venue->getAddress();
                $contact = $venue->getContact();

                $post_code_and_city = trim($this->nullToString($address->getPostcode()) . " " . $this->nullToString($address->getCity()));
                $mail = $this->nullToString($contact->getEmail());
                $phone = $this->nullToString($contact->getPhone());

                $ret[] = $general->getName() . ", " . $address->getCity();
                $ret[] = $address->getAddress1();

                if ($post_code_and_city != "") {
                    $ret[] = $post_code_and_city;
                }

                if ($mail != "") {
                    $ret[] = $mail;
                }

                if ($phone != "") {
                    $ret[] = $phone;
                }
            }
        }
        return $ret;
    }

    /**
     * Check if a specific plugin is active
     *
     * @param string 		$name
     *
     * @return bool
     */
    public function isPluginActive($name)
    {
        assert('is_string($name)');
        return \ilPluginAdmin::isPluginActive($name);
    }

    /**
     * Transform null to empty string
     *
     * @param string | null 		$value
     * @return string
     */
    protected function nullToString($value)
    {
        if ($value === null) {
            return "";
        }
        return $value;
    }
}
