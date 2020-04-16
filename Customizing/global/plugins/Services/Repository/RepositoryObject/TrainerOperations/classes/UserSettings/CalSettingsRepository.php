<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\UserSettings;

use CaT\Plugins\TrainerOperations\UserSettings\DB as SettingsDB;
use CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar\CalendarRepository;
use CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar\CalendarCategory;
use CaT\Plugins\TrainerOperations\AccessHelper;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class CalSettingsRepository
{
    const GLOBAL_CAL_USR_ID = 0;
    /**
     * @var SettingsDB
     */
    protected $settings_db;

    /**
     * @var CalendarRepository
     */
    protected $il_cal_repo;

    /**
     * @var AccessHelper
     */
    protected $access;


    public function __construct(
        SettingsDB $settings_db,
        CalendarRepository $il_cal_repo,
        AccessHelper $access
    ) {
        $this->settings_db = $settings_db;
        $this->il_cal_repo = $il_cal_repo;
        $this->access = $access;
    }

    /**
     * @return CalendarSettings[]
     */
    public function getEnabledSettingsForUser(int $tep_obj_id, int $usr_id) : array
    {
        $il_cals = $this->getAllIliasCalendarCategories();
        $tep_cals = $this->getStoredSettingsForObjectAndUser($tep_obj_id, $usr_id);
        $settings = $this->getSettingsBasedOnTEPCals($tep_obj_id, $il_cals, $tep_cals);

        return $settings;
    }

    /**
     * @return CalendarSettings[]
     */
    public function getEnabledGlobalSettings(int $tep_obj_id) : array
    {
        $il_cals = $this->getAllIliasCalendarCategories();
        $tep_cals = $this->getStoredSettingsForObjectAndUser($tep_obj_id, self::GLOBAL_CAL_USR_ID);
        $settings = $this->getSettingsBasedOnTEPCals($tep_obj_id, $il_cals, $tep_cals);

        return $settings;
    }

    /**
     * @return CalendarSettings[]
     */
    public function getPossibleSettingsFor(int $tep_obj_id) : array
    {
        $tep_cals = $this->getStoredSettingsForObject($tep_obj_id);

        $allowed_cal_cat_ids = [];
        if ($this->access->mayEditOwnCalendars()) {
            $allowed_cal_cat_ids[] = $this->access->getCurrentUserId();
        }
        if ($this->access->mayEditGeneralCalendars()) {
            $allowed_cal_cat_ids[] = 0;
        }

        $il_cals = $this->getIliasCalendarCategories($allowed_cal_cat_ids);

        $settings = [];
        if ($il_cals) {
            $settings = $this->getSettingsBasedOnIlCals($tep_obj_id, $il_cals, $tep_cals);
        }
        return $settings;
    }

    /**
     * Add infos from tep_cals to il_cals.
     * @return CalendarSettings[]
     */
    protected function getSettingsBasedOnIlCals(
        int $tep_obj_id,
        array $il_cals,
        array $tep_cals
    ) : array {
        $lookup = [];
        foreach ($tep_cals as $tep_cal) {
            $id = $this->buildMatchIdForTEPSetting($tep_cal);
            $lookup[$id] = $tep_cal;
        }

        $settings = [];
        foreach ($il_cals as $il_cal) {
            $tep_obj_id = $tep_obj_id;
            $cat_id = $il_cal->getCategoryId();
            $usr_id = $il_cal->getObjId();
            $title = $il_cal->getTitle();
            $url = $il_cal->getRemoteUrl();
            $username = $il_cal->getRemoteUser();
            $password = $il_cal->getRemotePass();
            $use = false;
            $hide_details = false;
            $storage_id = -1;

            $match_id = $this->buildMatchIdForIlCalCategory($il_cal);
            if (array_key_exists($match_id, $lookup)) {
                $use = true;
                $hide_details = $lookup[$match_id]->getHideDetails();
                $storage_id = $lookup[$match_id]->getStorageId();
            }

            $settings[] = new CalendarSettings(
                $storage_id,
                $tep_obj_id,
                $cat_id,
                $usr_id,
                $title,
                $url,
                $username,
                $password,
                $use,
                $hide_details
            );
        }
        return $settings;
    }


    /**
     * Add infos from il_cals to tep_cals.
     * @return CalendarSettings[]
     */
    protected function getSettingsBasedOnTEPCals(
        int $tep_obj_id,
        array $il_cals,
        array $tep_cals
    ) : array {
        $lookup = [];
        foreach ($il_cals as $il_cal) {
            $match_id = $this->buildMatchIdForIlCalCategory($il_cal);
            $lookup[$match_id] = $il_cal;
        }


        $settings = [];
        foreach ($tep_cals as $tep_cal) {
            $id = $this->buildMatchIdForTEPSetting($tep_cal);

            $tep_obj_id = $tep_obj_id;
            $use = true;
            $hide_details = $tep_cal->getHideDetails();
            $storage_id = $tep_cal->getStorageId();

            $il_cal = $lookup[$id];
            $cat_id = $il_cal->getCategoryId();
            $usr_id = $il_cal->getObjId();
            $title = $il_cal->getTitle();
            $url = $il_cal->getRemoteUrl();
            $username = $il_cal->getRemoteUser();
            $password = $il_cal->getRemotePass();

            $settings[] = new CalendarSettings(
                $storage_id,
                $tep_obj_id,
                $cat_id,
                $usr_id,
                $title,
                $url,
                $username,
                $password,
                $use,
                $hide_details
            );
        }
        return $settings;
    }

    protected function buildMatchIdForTEPSetting(TEPCalendarSettings $tep_cal) : string
    {
        return implode(
            '#',
            [
                    $tep_cal->getCalCatId(),
                    $tep_cal->getUserId()
                ]
        );
    }

    protected function buildMatchIdForIlCalCategory(CalendarCategory $il_cal) : string
    {
        return implode(
            '#',
            [
                    $il_cal->getCategoryId(),
                    $il_cal->getObjId()
                ]
        );
    }

    /**
     * Get all Ilias-Calendars
     * @return Aggregations\IliasCalendar\CalendarCategory[];
     */
    protected function getAllIliasCalendarCategories() : array
    {
        return $this->il_cal_repo->getCategories();
    }

    /**
     * Get all Ilias-Calendars
     * @return Aggregations\IliasCalendar\CalendarCategory[];
     */
    protected function getIliasCalendarCategories(array $ids) : array
    {
        return $this->il_cal_repo->getCategoriesByObjIds($ids);
    }

    /**
     * Get all the stored settings for a TEP object
     * @return TEPCalendarSettings[];
     */
    protected function getStoredSettingsForObject(int $tep_obj_id) : array
    {
        return $this->settings_db->selectFor($tep_obj_id);
    }

    /**
     * Get the stored settings for a TEP object and a specific user
     * @return TEPCalendarSettings[];
     */
    protected function getStoredSettingsForObjectAndUser(
        int $tep_obj_id,
        int $usr_id
    ) : array {
        return $this->settings_db->selectForUser($tep_obj_id, $usr_id);
    }

    /**
     * @param TEPCalendarSettings[] $tep_cal_settings
     */
    public function updateTepUserSettings(array $tep_cal_settings)
    {
        $this->settings_db->store($tep_cal_settings);
    }

    public function deleteCalendar(int $cal_catid)
    {
        $this->settings_db->deleteUserSettings($cal_catid);
        $this->il_cal_repo->deleteCatagory($cal_catid);
    }

    public function getCategory(int $cal_catid)
    {
        return $this->il_cal_repo->getCategoryById($cal_catid);
    }
}
