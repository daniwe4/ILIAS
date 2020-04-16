<?php

namespace CaT\Plugins\UserBookings;

require_once("Services/Component/classes/class.ilPluginAdmin.php");

/**
 * cat-tms-patch start
 */

class Helper
{
    const F_TITLE = "f_title";
    const F_TYPE = "f_type";
    const F_TOPIC = "f_topic";
    const F_TARGET_GROUP = "f_target";
    const F_CITY = "f_city";
    const F_PROVIDER = "f_provider";
    const F_NOT_MIN_MEMBER = "f_not_min_member";
    const F_DURATION = "f_duration";

    /**
     * @var ilObjUser
     */
    protected $g_user;

    public function __construct()
    {
        global $DIC;
        $this->g_user = $DIC->user();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_lng = $DIC->language();
        $this->g_factory = $DIC->ui()->factory();
        $this->g_renderer = $DIC->ui()->renderer();
    }

    /**
     * Get needed values from bkm. Just best for user
     *
     * @param ilObjBookingModalities[] 	$bms
     * @param ilDateTime 	$crs_start_date
     *
     * @return array<integer, int | ilDateTime | string>
     */
    public function getBestBkmValues(array $bkms, \ilDateTime $crs_start_date)
    {
        $plugin = \ilPluginAdmin::getPluginObjectById('xbkm');
        return $plugin->getBestBkmValues($bkms, $crs_start_date);
    }

    /**
     * Get information about selected venue
     *
     * @param int 	$crs_id
     *
     * @return string[]
     */
    public function getVenueInfos($crs_id)
    {
        $plugin = \ilPluginAdmin::getPluginObjectById('venues');
        if (!$plugin) {
            return array(-1,"", "");
        }

        return $plugin->getVenueInfos($crs_id);
    }

    /**
     * Get information about selected provider
     *
     * @param int 	$crs_id
     *
     * @return string[]
     */
    public function getProviderInfos($crs_id)
    {
        $plugin = \ilPluginAdmin::getPluginObjectById('trainingprovider');
        if (!$plugin) {
            return array(-1);
        }

        return $plugin->getProviderInfos($crs_id);
    }

    /**
     * Get information from course classification object
     *
     * @param ilObjCourseClassification 	$ccl
     *
     * @return array<integer, string | int[] | string[] | null>
     */
    public function getCourseClassificationValues($ccl)
    {
        if ($ccl === null) {
            return array(null,
                "",
                array(),
                array(),
                "",
                array(),
                array(),
            );
        }

        return $ccl->getCourseClassificationValues();
    }

    /**
     * Form date for gui as user timezone string
     *
     * @param ilDateTime 	$dat
     * @param bool 	$use_time
     *
     * @return string
     */
    public function formatDate($dat, $use_time = false)
    {
        require_once("Services/Calendar/classes/class.ilCalendarUtil.php");
        $out_format = \ilCalendarUtil::getUserDateFormat($use_time, true);
        $ret = $dat->get(IL_CAL_FKT_DATE, $out_format, $this->g_user->getTimeZone());
        if (substr($ret, -5) === ':0000') {
            $ret = substr($ret, 0, -5);
        }

        return $ret;
    }
}

/**
 * cat-tms-patch end
 */
