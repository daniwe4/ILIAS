<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use CaT\Plugins\TrainerOperations\Calendar\Calendar;
use CaT\Plugins\TrainerOperations\Calendar\Schedule;
use ILIAS\UI\Component as UIComponent;

/**
 * Render the calendar.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class CalRenderer
{
    const DAY_OF_WEEK_SATURDAY = 6;
    const DAY_OF_WEEK_SUNDAY = 7;

    const MAX_LENGTH_DESCRIPTION = 50;

    const ASYNC_CMD_SESSION_MODAL = 'sessionmodal';
    const F_SESSION_REF_ID = 'sessref';
    const F_COURSE_REF_ID = 'crsid';

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilTemplate
     */
    protected $cal_tpl;

    /**
     * @var \ilTemplate
     */
    protected $cell_tpl;

    /**
     * @var \ilTemplate
     */
    protected $event_tpl;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var DefaultRenderer
     */
    protected $ui_renderer;

    /**
     * @var int
     */
    protected $current_user_id;

    public function __construct(
        \Closure $txt,
        \ilTemplate $cal_template,
        \ilTemplate $cell_template,
        \ilTemplate $event_template,
        \ILIAS\UI\Implementation\Factory $ui_factory,
        \ILIAS\UI\Implementation\DefaultRenderer $ui_renderer,
        int $current_user_id
    ) {
        $this->txt = $txt;
        $this->cal_tpl = $cal_template;
        $this->cell_tpl = $cell_template;
        $this->event_tpl = $event_template;
        $this->session_form_template = $session_form_template;

        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;

        $this->current_user_id = $current_user_id;
        $this->modals = [];
    }

    public function render(Calendar $calendar) : string
    {
        $schedules = $calendar->getSchedules();
        $days = $calendar->getRange();
        $counter = 0;
        foreach ($schedules as $schedule) {
            $label = $schedule->getTitle();
            $this->cal_tpl->setCurrentBlock('header_cell');
            $this->cal_tpl->setVariable('COL_LABEL', stripslashes($label));
            $this->cal_tpl->parseCurrentBlock();
        }

        foreach ($days as $day) {
            $row = clone $this->cell_tpl;
            $counter++;

            foreach ($schedules as $schedule) {
                $cell_content = $this->renderCell($day, $schedule);
                $row->setCurrentBlock('day_cell');
                $row->setVariable('DAY_DATE', $this->formatDate($day));
                $row->setVariable('DAY_CONTENT', $cell_content);
                $row->parseCurrentBlock();
            }

            $this->cal_tpl->setCurrentBlock('day_row');
            $this->cal_tpl->setVariable('ROW', $row->get());

            if (in_array($day->format('N'), [self::DAY_OF_WEEK_SATURDAY, self::DAY_OF_WEEK_SUNDAY])) {
                $this->cal_tpl->setVariable('ROWSTYLE', 'weekend');
            }

            $this->cal_tpl->parseCurrentBlock('day_row_end');
        }

        return $this->cal_tpl->get();
    }

    protected function renderCell(\DateTime $day, Schedule $schedule) : string
    {
        $content = [];
        $tpl = clone $this->event_tpl;
        foreach ($schedule->getEntryByDay($day) as $event) {
            $modal = false;
            $tpl->setCurrentBlock('event');

            $title = stripslashes($event->getTitle());
            $description = '';
            $type = $event->getType();


            if ($event->getType() === Entry::TYPE_PERSONAL) {
                if ($event->getPrivate()) {
                    $type .= ' undisclosed';
                    if ($event->getUserId() === $this->current_user_id) {
                        $type .= ' mine';
                        $modal = $this->getModalForExternalEvent($event);
                    } else {
                        $title = $this->txt('undiclosed_title');
                    }
                } else {
                    $modal = $this->getModalForExternalEvent($event);
                }
            }


            if ($event->getType() === Entry::TYPE_SESSION) {
                $description = $this->formatDescription($event->getDescription(), self::MAX_LENGTH_DESCRIPTION);

                $modal = $this->ui_factory->modal()->roundtrip('', $this->ui_factory->legacy(''))
                    ->withAsyncRenderUrl(
                        $this->getSessionModalUrl($event->getSessionRefId(), $event->getCrsRefId())
                    );

                $event_id = 'crs' . $event->getCrsRefId();
                $tpl->setVariable('EVENT_ID', $event_id);
                $tpl->setVariable('EVENT_DATAID', $event_id);
            }


            if ($modal) {
                $this->modals[] = $modal;
                $lnk = $this->ui_factory->button()->shy($title, '#')
                    ->withOnClick($modal->getShowSignal());
                $title = $this->ui_renderer->render($lnk);
            }

            $tpl->setVariable('EVENT_TYPE', $type);
            $tpl->setVariable('EVENT_TITLE', $title);
            $tpl->setVariable('EVENT_DESCRIPTION', $description);

            if ($event->isFullDay()) {
                $tpl->setVariable('FULLDAY', $this->txt('fullday'));
            } else {
                $event_time = $this->formatDuration($event->getStart(), $event->getEnd());
                $tpl->setVariable('EVENT_TIME', $event_time);
            }

            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('MODALS', $this->ui_renderer->render[$modals]);
        return $tpl->get();
    }

    protected function getSessionModalUrl(int $session_ref_id, int $crs_ref_id) : string
    {
        $modal_async_url = $_SERVER['REQUEST_URI'];
        $base = substr($modal_async_url, 0, strpos($modal_async_url, '?') + 1);
        $query = parse_url($modal_async_url, PHP_URL_QUERY);
        parse_str($query, $params);

        $params['cmd'] = self::ASYNC_CMD_SESSION_MODAL;
        $params[self::F_SESSION_REF_ID] = $session_ref_id;
        $params[self::F_COURSE_REF_ID] = $crs_ref_id;
        $modal_async_url = $base . http_build_query($params);
        return $modal_async_url;
    }

    protected function getModalForExternalEvent(PersonalEntry $event) : UIComponent\Modal\RoundTrip
    {
        if ($event->isFullDay()) {
            $duration = $this->txt('fullday');
        } else {
            $duration = $this->formatDuration($event->getStart(), $event->getEnd())
             . ' '
             . $this->txt('oclock');
        }
        $date_start = $this->formatDate($event->getStart());
        $date_end = $this->formatDate($event->getEnd());
        if ($date_start === $date_end) {
            $date = $date_start;
        } else {
            $date = $date_start . ' - ' . $date_end;
        }

        $details = [
            $this->txt('title') => $event->getTitle(),
            $this->txt('subtitle') => $event->getSubtitle(),
            $this->txt('date') => $date,
            $this->txt('duration') => $duration,
            $this->txt('location') => $this->formatDescription($event->getLocation()),
            $this->txt('informations') => $this->formatDescription($event->getInformations()),
            $this->txt('description') => $this->formatDescription($event->getDescription())
        ];
        $listing = $this->ui_factory->listing()->descriptive($details);
        $modal = $this->ui_factory->modal()->roundtrip($this->txt('modal_external_title'), $listing);
        return $modal;
    }


    protected function formatDescription(string $description, int $maxlength = 0) : string
    {
        $description = nl2br($description);
        $description = str_replace('\n', '<br / >', $description);
        if ($maxlength > 0 && strlen($description) > $maxlength) {
            $cut = strpos($description, ' ', $maxlength);
            if ($cut !== false) {
                $description = substr($description, 0, $cut);
            } else {
                $description = substr($description, 0, $maxlength);
            }
            $description .= '[...]';
        }
        return $description;
    }

    protected function formatDuration(\DateTime $start, \DateTime $end) : string
    {

        //TODO: timezone of user instead of fixed tz!
        $tz = new \DateTimeZone('Europe/Berlin');
        $start->setTimezone($tz);
        $end->setTimezone($tz);

        $time_format = 'H:i';
        return $start->format($time_format) . '-' . $end->format($time_format);
    }

    protected function formatDate(\DateTime $date) : string
    {
        $weekday = $date->format('D');
        $date_format = 'd.m.Y'; //TODO: user format
        return $this->txt($weekday) . ', ' . $date->format($date_format);
    }

    protected function getDays() : \DatePeriod
    {
        $period = new \DatePeriod(
            $this->start,
            new \DateInterval('P1D'),
            $this->end
        );
        return $period;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }

    public function getModals()
    {
        return $this->modals;
    }
}
