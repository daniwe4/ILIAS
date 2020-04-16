<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar\CalendarRepository;
use CaT\Plugins\TrainerOperations\Aggregations\IliasCalendar\Entry;
use CaT\Plugins\TrainerOperations\UserSettings\CalSettingsRepository;

/**
 * Get events from other Ilias Calendars
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class IliasEntryRepository
{
    public function __construct(
        CalendarRepository $cal_accessor,
        CalSettingsRepository $settings
    ) {
        $this->cal_accessor = $cal_accessor;
        $this->settings = $settings;
    }

    /**
     * @return Entry[] either personal or private
     */
    public function getAllPersonalEvents(int $tep_obj_id, int $usr_id) : array
    {
        $settings = $this->settings->getEnabledSettingsForUser($tep_obj_id, $usr_id);

        $public = array_filter($settings, function ($set) {
            return $set->getHideDetails() === false;
        });
        $undisclosed = array_filter($settings, function ($set) {
            return $set->getHideDetails();
        });


        $cal_ids_public = array_map(
            function ($set) {
                return $set->getCalCatId();
            },
            $public
        );
        $cal_ids_undisclosed = array_map(
            function ($set) {
                return $set->getCalCatId();
            },
            $undisclosed
        );

        $entries = [];

        $events = $this->cal_accessor->getEvents($cal_ids_public);
        foreach ($events as $event) {
            $entries[] = $this->buildIliasPersonalEntry($event, $usr_id, false);
        }
        $events = $this->cal_accessor->getEvents($cal_ids_undisclosed);
        foreach ($events as $event) {
            $entries[] = $this->buildIliasPersonalEntry($event, $usr_id, true);
        }

        return $entries;
    }

    /**
     * @return GeneralEntry[]
     */
    public function getAllGeneralEvents(int $tep_obj_id) : array
    {
        $settings = $this->settings->getEnabledGlobalSettings($tep_obj_id);
        $cal_ids = array_map(
            function ($set) {
                return $set->getCalCatId();
            },
            $settings
        );

        $entries = [];
        $events = $this->cal_accessor->getEvents($cal_ids);
        foreach ($events as $event) {
            $entries[] = $this->buildIliasGlobalEntry($event);
        }

        return $entries;
    }

    protected function buildIliasGlobalEntry(Entry $entry) : GlobalEntry
    {
        return new GlobalEntry(
            $entry->getId(),
            $entry->getTitle(),
            $entry->getDescription(),
            $entry->getFullday(),
            $entry->getStart(),
            $entry->getEnd()
        );
    }

    protected function buildIliasPersonalEntry(Entry $entry, int $usr_id, bool $undisclosed) : PersonalEntry
    {
        return new PersonalEntry(
            $usr_id,
            $undisclosed,
            $entry->getId(),
            $entry->getTitle(),
            $entry->getDescription(),
            $entry->getFullday(),
            $entry->getStart(),
            $entry->getEnd(),
            $entry->getLocation(),
            $entry->getInformations(),
            $entry->getSubtitle()
        );
    }
}
