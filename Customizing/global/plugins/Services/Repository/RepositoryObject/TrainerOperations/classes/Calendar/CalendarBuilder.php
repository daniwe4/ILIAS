<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use CaT\Plugins\TrainerOperations\AccessHelper;
use CaT\Plugins\TrainerOperations\Aggregations\User;
use CaT\Plugins\TrainerOperations\UserSettings\CalSettingsRepository;

/**
 * Build a calendar
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalendarBuilder
{
    const ID_UNASSIGNED = 'unassigned';
    const ID_INACCESSIBLE = 'inaccessible';
    const ID_GLOBAL = 'global';

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var User
     */
    protected $user_utils;

    /**
     * @var SessionEntryRepository
     */
    protected $sessions_entries;

    /**
     * @var IliasEntryRepository
     */
    protected $ilias_entries;

    /**
     * @var AccessHelper
     */
    protected $access;

    /**
     * @var int
     */
    protected $tep_obj_id;
    /**
     * @var int[]
     */
    protected $users;
    /**
     * @var \DatePeriod
     */
    protected $range;
    /**
     * @var Calendar
     */
    protected $calendar;
    /**
     * @var string[]
     */
    protected $selected_cols;


    public function __construct(
        \Closure $txt,
        User $user_utils,
        SessionEntryRepository $sessions_entries,
        IliasEntryRepository $ilias_entries,
        AccessHelper $access
    ) {
        $this->txt = $txt;
        $this->user_utils = $user_utils;
        $this->settings_repo = $settings_repo;
        $this->sessions_entries = $sessions_entries;
        $this->ilias_entries = $ilias_entries;
        $this->access = $access;
    }

    public function configure(CalConfig $config) : CalendarBuilder
    {
        $this->tep_obj_id = $config->getTEPObjId();
        $this->sessions_entries = $this->sessions_entries
            ->withBaseRefId(
                $config->getBaseRefId(),
                $config->getStart(),
                $config->getEnd()
            );

        $this->users = $config->getUserIds(); //TODO: re-verify?
        $this->range = $this->buildPeriod($config->getStart(), $config->getEnd());
        $this->selected_cols = $config->getSelectedColumns();

        $this->calendar = new Calendar($this->range, $this->selected_cols);
        $this->build();
        return $this;
    }

    protected function build()
    {
        $schedules = $this->buildSchedules();
        $this->calendar = $this->calendar->withSchedules($schedules);
    }

    protected function buildSchedules()
    {
        $schedules = [];

        if ($this->access->maySeeUnassingedDates()
            && $this->selected_cols[self::ID_UNASSIGNED]
        ) {
            $schedules[] = $this->buildUnassignedSchedule();
        }

        if ($this->access->maySeeForeignCalendars()
            && $this->selected_cols[self::ID_INACCESSIBLE]) {
            $schedules[] = $this->buildInaccessibleSchedule();
        }

        if ($this->selected_cols[self::ID_GLOBAL]) {
            $schedules[] = $this->buildGeneralSchedule();
        }

        foreach ($this->users as $usr_id) {
            if ($this->selected_cols[(string) $usr_id]) {
                $schedules[] = $this->buildUserSchedule($usr_id);
            }
        }

        return $schedules;
    }

    protected function buildUnassignedSchedule()
    {
        $entries = $this->sessions_entries->getAllSessionEntriesWithoutTutor();
        $title = $this->txt('schedule_unassigned');
        return new Schedule(self::ID_UNASSIGNED, $title, $entries);
    }

    protected function buildInaccessibleSchedule()
    {
        $entries = $this->sessions_entries->getAllSessionEntriesWithoutIdentifiedTutor();
        $title = $this->txt('schedule_inaccessible');
        return new Schedule(self::ID_INACCESSIBLE, $title, $entries);
    }

    protected function buildGeneralSchedule()
    {
        $entries = $this->ilias_entries->getAllGeneralEvents($this->tep_obj_id);
        $title = $this->txt('schedule_general');
        return new Schedule(self::ID_GLOBAL, $title, $entries);
    }

    protected function buildUserSchedule(int $usr_id) : Schedule
    {
        $session_entries = $this->sessions_entries->getAllSessionEntriesWithTutor($usr_id);
        $personal_entries = $this->ilias_entries->getAllPersonalEvents($this->tep_obj_id, $usr_id);

        $entries = array_merge($session_entries, $personal_entries);
        $title = $this->user_utils->getDisplayName($usr_id);
        return new Schedule((string) $usr_id, $title, $entries);
    }

    protected function buildPeriod(\DateTime $start, \DateTime $end) : \DatePeriod
    {
        $start->setTime(0, 0);
        $end->setTime(0, 0);
        $end->modify('+1 day');

        $range = new \DatePeriod(
            $start,
            new \DateInterval('P1D'),
            $end
        );

        return $range;
    }

    public function getCalendar() : Calendar
    {
        return $this->calendar;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
