<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\Mailing\ContentBlocks;

use CaT\Plugins\StatusMails\History\UserActivity;
use CaT\Plugins\StatusMails\Course\CourseFlags;

/**
 * Render a chunk of text to append to a mail-body.
 * @author Nils Haagen    <nils.haagen@concepts-and-training.de>
 */
class TemplateUserEntry
{
    const ROW_USER = '[FIRST_NAME] [LAST_NAME] ([LOGIN])';
    const ROW_COURSE = '[COURSE_TYPE]: [COURSE_TITLE]';
    const ROW_COURSE_DATES = 'Trainingszeitraum: [COURSE_START_DATE] – [COURSE_END_DATE]';
    const ROW_OVERNIGHS = <<<HTM
Übernachtungen:
[OVERNIGHTS]
Vorabendanreise: [PRIOR]
Abreise am Folgetag: [FOLLOWING]
HTM;
    const ROW_NO_OVERNIGHS = 'Übernachtungen: - ';
    const ROW_COURSE_IDD = 'max. IDD-Zeit: [IDD_TIME]';
    const ROW_USER_IDD = 'geleistete IDD-Zeit: [IDD_USER_TIME]';
    const YES = 'Ja';
    const NO = 'Nein';

    public function apply(UserActivity $activity, CourseFlags $flags) : string
    {
        $buffer = array(
            $this->rowUserInfo($activity),
            $this->rowCourseInfo($activity)
        );

        $crs_start = $activity->getCourseStartDate();
        $crs_end = $activity->getCourseEndDate();
        if (
            !is_null($crs_start) &&
            !is_null($crs_end)
        ) {
            $buffer[] = $this->rowCourseDates($activity);
        }

        if ($flags->outlineOvernights()) {
            $buffer[] = $this->rowOvernights($activity);
        }

        if (
        in_array(
            $activity->getActivityType(),
            [
                UserActivity::ACT_TYPE_BOOKED,
                UserActivity::ACT_TYPE_BOOKED_WAITING,
                UserActivity::ACT_TYPE_COMPLETED
            ]
        )
        ) {
            $buffer[] = $this->rowCourseIDD($activity);
        }

        if ($activity->getActivityType() == UserActivity::ACT_TYPE_COMPLETED) {
            $buffer[] = $this->rowUserIDD($activity);
        }

        return implode('<br>', $buffer);
    }

    protected function rowUserInfo(UserActivity $activity) : string
    {
        return $this->replace(self::ROW_USER, array(
            'FIRST_NAME' => $activity->getUserFirstName(),
            'LAST_NAME' => $activity->getUserLastName(),
            'LOGIN' => $activity->getUserLogin()
        ));
    }

    /**
     * Replace placeholder in text with values from array.
     * @param string[] $vars
     */
    protected function replace(string $txt, array $vars) : string
    {
        foreach ($vars as $key => $value) {
            $txt = str_replace(
                '[' . strtoupper($key) . ']',
                $value,
                $txt
            );
        }
        return $txt;
    }

    protected function rowCourseInfo(UserActivity $activity) : string
    {
        $type = $activity->getCourseType();
        //do something with type...
        return $this->replace(self::ROW_COURSE, array(
            'COURSE_TYPE' => $type,
            'COURSE_TITLE' => $activity->getCourseTitle()
        ));
    }

    protected function rowCourseDates(UserActivity $activity) : string
    {
        $crs_start = $activity->getCourseStartDate();
        $crs_end = $activity->getCourseEndDate();
        return $this->replace(self::ROW_COURSE_DATES, array(
            'COURSE_START_DATE' => $crs_start->format('d.m.Y'),
            'COURSE_END_DATE' => $crs_end->format('d.m.Y')
        ));
    }

    protected function rowOvernights(UserActivity $activity) : string
    {
        if (count($activity->getUserOvernights()) === 0) {
            return self::ROW_NO_OVERNIGHS;
        }
        $overnights = [];
        $prior = self::NO;
        $following = self::NO;

        foreach ($activity->getUserOvernights() as $oa) {
            $overnights[] = $oa->format('d.m.Y');
        }
        $overnights = implode('<br /> ', $overnights);

        if ($activity->getPriorNight()) {
            $prior = self::YES;
        }
        if ($activity->getFollowingNight()) {
            $following = self::YES;
        }

        return $this->replace(self::ROW_OVERNIGHS, array(
            'OVERNIGHTS' => $overnights,
            'PRIOR' => $prior,
            'FOLLOWING' => $following
        ));
    }

    protected function rowCourseIDD(UserActivity $activity) : string
    {
        return $this->replace(self::ROW_COURSE_IDD, array(
            'IDD_TIME' => $activity->getCourseIDDTime()
        ));
    }

    protected function rowUserIDD(UserActivity $activity) : string
    {
        $idd_user_time = $activity->getUserIDDTime();

        if ($idd_user_time === '00:00') {
            $idd_user_time = $activity->getCourseIDDTime();
        }
        return $this->replace(self::ROW_USER_IDD, array(
            'IDD_USER_TIME' => $idd_user_time
        ));
    }
}
