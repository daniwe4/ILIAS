<?php

namespace ILIAS\TMS\Mailing;

/**
 * Class ilTMSICalBuilder
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class EluceoICalBuilder implements ICalBuilder
{
    const TITLE = "Titel";
    const DESCRIPTION = "Beschreibung";
    const DATE = "Datum";
    const VENUE = "Veranstalter";
    const TIME = "Zeit";
    const FILE_EXTENSION = ".ics";

    /**
     * @var	string
     */
    protected $client_id;

    /**
     * @var	string
     */
    protected $organizer_name;

    /**
     * @var	string
     */
    protected $organizer_email;

    public function __construct(string $client_id, string $organizer_name, string $organizer_email)
    {
        $this->client_id = $client_id;
        $this->organizer_name = $organizer_name;
        $this->organizer_email = $organizer_email;
    }

    /**
     * @inheritdoc
     */
    public function getICalString(string $ref, array $info) : string
    {
        $crs_name = "";
        $description = "";
        $duration = "";
        $times = "";
        $venue = "";

        foreach ($info as $i) {
            switch ($i->getLabel()) {
                case self::TITLE:
                    $title = $i->getValue();
                    break;
                case self::DESCRIPTION:
                    $description = $i->getValue();
                    break;
                case self::DATE:
                    $date = $i->getValue();
                    break;
                case self::TIME:
                    $times = $i->getValue();
                    break;
                case self::VENUE:
                    $venue = $i->getValue();
                    break;
                default:
                    break;
            }
        }

        $tz = $this->getDefaultTimezone();
        $organizer = $this->getOrganizer();

        $calendar = new \Eluceo\iCal\Component\Calendar($title);
        $calendar
            ->setTimezone($tz)
            ->setMethod(\Eluceo\iCal\Component\Calendar::METHOD_PUBLISH)
            ->setCalId($this->client_id);

        if ($times === "") {
            $event = new \Eluceo\iCal\Component\Event($this->getEventUID($ref));
            $event
                ->setDtStart(new \DateTime($date['start']))
                ->setDtEnd(new \DateTime($date['end']))
                ->setNoTime(false)
                ->setLocation($venue, $venue)
                ->setSummary($title)
                ->setDescription($description)
                ->setOrganizer($organizer)
                ->setModified(new \DateTime());
            $calendar->addComponent($event);
        } else {
            // This counter is for creating uids per session.
            $i = 0;
            foreach ($times as $time) {
                $start = $time['date'] . " " . $time['start_time'] . ":00";
                $end = $time['date'] . " " . $time['end_time'] . ":00";

                $event = new \Eluceo\iCal\Component\Event($this->getEventUID($ref) . "-$i");
                $event
                    ->setDtStart(new \DateTime($start))
                    ->setDtEnd(new \DateTime($end))
                    ->setNoTime(false)
                    ->setLocation($venue, $venue)
                    ->setSummary($title)
                    ->setDescription($description)
                    ->setOrganizer($organizer)
                    ->setModified(new \DateTime());
                $calendar->addComponent($event);

                $i++;
            }
        }
        return $calendar->render();
    }

    /**
     * @inheritdoc
     */
    public function saveICal(string $ref, array $info, string $file_name) : string
    {
        $ical = $this->getICalString($ref, $info);

        $tmp_folder = sys_get_temp_dir();
        if (substr($tmp_folder, -1) != "/") {
            $tmp_folder .= "/";
        }
        $tmp_folder .= uniqid();
        mkdir($tmp_folder, 0700, true);

        $tmp = $tmp_folder . "/" . $file_name . self::FILE_EXTENSION;
        file_put_contents($tmp, $ical);
        return $tmp;
    }

    protected function getEventUID(string $ref) : string
    {
        return $this->client_id . "-" . $ref;
    }

    protected function getDefaultTimezone()
    {
        $tz_rule_daytime = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_DAYLIGHT);
        $tz_rule_daytime
            ->setTzName('CEST')
            ->setDtStart(new \DateTime('1981-03-29 02:00:00'))
            ->setTzOffsetFrom('+0100')
            ->setTzOffsetTo('+0200');
        $tz_rule_daytime_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
        $tz_rule_daytime_rec
            ->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
            ->setByMonth(3)
            ->setByDay('-1SU');
        $tz_rule_daytime->setRecurrenceRule($tz_rule_daytime_rec);
        $tz_rule_standard = new \Eluceo\iCal\Component\TimezoneRule(\Eluceo\iCal\Component\TimezoneRule::TYPE_STANDARD);
        $tz_rule_standard
            ->setTzName('CET')
            ->setDtStart(new \DateTime('1996-10-27 03:00:00'))
            ->setTzOffsetFrom('+0200')
            ->setTzOffsetTo('+0100');
        $tz_rule_standard_rec = new \Eluceo\iCal\Property\Event\RecurrenceRule();
        $tz_rule_standard_rec
            ->setFreq(\Eluceo\iCal\Property\Event\RecurrenceRule::FREQ_YEARLY)
            ->setByMonth(10)
            ->setByDay('-1SU');
        $tz_rule_standard->setRecurrenceRule($tz_rule_standard_rec);
        $tz = new \Eluceo\iCal\Component\Timezone('Europe/Berlin');
        $tz->addComponent($tz_rule_daytime);
        $tz->addComponent($tz_rule_standard);
        return $tz;
    }

    protected function getOrganizer() : \Eluceo\iCal\Property\Event\Organizer
    {
        return new \Eluceo\iCal\Property\Event\Organizer(
            "mailto:{$this->organizer_email}",
            ["CN" => $this->organizer_name]
        );
    }
}
