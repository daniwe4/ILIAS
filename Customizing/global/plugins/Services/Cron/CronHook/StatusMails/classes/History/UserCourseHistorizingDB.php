<?php

declare(strict_types=1);

namespace CaT\Plugins\StatusMails\History;

use ILIAS\TMS\Filter;
use ILIAS\TMS\TableRelations;

class UserCourseHistorizingDB implements DB
{
    const ACTIVITY = 'activity';
    const NIGHTS_DELIMITER = ';';

    const HEAD_COURSE_TABLE = 'hhd_crs';
    const HEAD_USERCOURSE_NIGHTS_TABLE = 'hhd_usrcrs_nights';
    const HEAD_USERCOURSE_TABLE = 'hhd_usrcrs';
    const USR_DATA_TABLE = 'usr_data';

    /**
     * @var TableRelations\GraphFactory
     */
    protected $gf;

    /**
     * @var Filter\PredicateFactory
     */
    protected $pf;

    /**
     * @var TableRelations\TableFactory
     */
    protected $tf;

    /**
     * @var Filter\TypeFactory
     */
    protected $tyf;

    /**
     * @var Filter\FilterFactory
     */
    protected $ff;

    /**
     * @var TableRelations\SqlQueryInterpreter
     */
    protected $interpreter;

    public function __construct(\ilDBInterface $ilDB)
    {
        $this->gf = new TableRelations\GraphFactory();
        $this->pf = new Filter\PredicateFactory();
        $this->tf = new TableRelations\TableFactory($this->pf, $this->gf);
        $this->tyf = new Filter\TypeFactory();
        $this->ff = new Filter\FilterFactory($this->pf, $this->tyf);
        $this->interpreter = new TableRelations\SqlQueryInterpreter(
            new Filter\SqlPredicateInterpreter($ilDB),
            $this->pf,
            $ilDB
        );
    }

    /**
     * @inheritdoc
     */
    public function getActivitiesByTime(\DateTime $start, \DateTime $end, array $usr_ids) : array
    {
        $return = [];
        if (count($usr_ids) === 0) {
            return $return;
        }

        foreach ($this->interpreter->interpret(
            $this->getActivitiesSpace($start, $end, $usr_ids)->query()
        ) as $row) {
            switch ($row['booking_status']) {
                case 'participant':
                    switch ($row['participation_status']) {
                        case 'successful':
                            $row[self::ACTIVITY] = UserActivity::ACT_TYPE_COMPLETED;
                            break;
                        case 'absent':
                            $row[self::ACTIVITY] = UserActivity::ACT_TYPE_FAILED;
                            break;
                        default:
                            $row[self::ACTIVITY] = UserActivity::ACT_TYPE_BOOKED;
                    }
                    break;
                case 'cancelled':
                case 'cancelled_after_deadline':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_CANCELLED;
                    break;
                case 'waiting':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_BOOKED_WAITING;
                    break;
                case 'waiting_cancelled':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_CANCELLED_WAITING;
                    break;
                case 'approval_pending':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_REQUEST_PENDING;
                    break;
                case 'approval_declined':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_REQUEST_DECLINED;
                    break;
                case 'approval_approved':
                    $row[self::ACTIVITY] = UserActivity::ACT_TYPE_REQUEST_APPROVED;
                    break;

                default:
                    // apparently we may not classify this entry as one of the known activity-types
                    // since we do not know where to put it: skip.
                    continue 2;
            }

            $return[] =
                new UserActivity(
                    $row[self::ACTIVITY],
                    (int) $row['usr_id'],
                    (int) $row['crs_id'],
                    $row['firstname'],
                    $row['lastname'],
                    $row['login'],
                    (string) $row['crs_type'],
                    $row['title'],
                    $this->extractDate($row['begin_date']),
                    $this->extractDate($row['end_date']),
                    $this->formatIDDTime((int) $row['idd_usr']),
                    $this->formatIDDTime((int) $row['idd_crs']),
                    $this->extractOvernights($row['nights']),
                    (bool) $row['prior_night'],
                    (bool) $row['following_night']
                );
        }
        return $return;
    }

    /**
     * Space containing all relevant data including "activities" constraints.
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int[]     $usr_ids
     * @return    TableRelations\TableSpace
     * @throws    \LogicException    if no usr_ids were given
     */
    protected function getActivitiesSpace(\DateTime $start, \DateTime $end, array $usr_ids)
    {
        if (count($usr_ids) === 0) {
            throw new \LogicException('Query not possible without usr_ids.');
        }

        $space = $this->getCommonSpace($usr_ids);
        $participations = $space->table('usrcrs');
        $participations->addConstraint(
            $participations->field('booking_status')->IN($this->pf->list_string_by_array(
                [
                    'participant'
                    ,
                    'waiting'
                    ,
                    'cancelled_after_deadline'
                    ,
                    'waiting_cancelled'
                    ,
                    'approval_pending'
                    ,
                    'approval_approved'
                    //,'approval_declined'
                ]
            ))
        );
        $participations->addConstraint(
            $participations->field('created_ts')->GE()->int($start->getTimestamp())
                           ->_AND($participations->field('created_ts')->LE()->int($end->getTimestamp()))
        );
        return $space;
    }

    /**
     * Space containing all relevant data.
     * @param int[] $usr_ids
     * @return    TableRelations\TableSpace
     */
    protected function getCommonSpace(array $usr_ids)
    {
        $crs_data = $this->tf->Table(self::HEAD_COURSE_TABLE, 'crs')
                             ->addField($this->tf->field('crs_id'))
                             ->addField($this->tf->field('title'))
                             ->addField($this->tf->field('crs_type'))
                             ->addField($this->tf->field('begin_date'))
                             ->addField($this->tf->field('end_date'))
                             ->addField($this->tf->field('idd_learning_time'));

        $nights_src = $this->tf->Table(self::HEAD_USERCOURSE_NIGHTS_TABLE, 'nights_src')
                               ->addField($this->tf->field('crs_id'))
                               ->addField($this->tf->field('usr_id'))
                               ->addField($this->tf->field('list_data'));
        $nights_space = $this->tf->TableSpace()
                                 ->addTablePrimary($nights_src)
                                 ->setRootTable($nights_src)
                                 ->groupBy($nights_src->field('crs_id'))
                                 ->groupBy($nights_src->field('usr_id'));
        $nights_space->request($nights_src->field('crs_id'))
                     ->request($nights_src->field('usr_id'))
                     ->request($this->tf->groupConcat(
                         'nights',
                         $this->tf->dateFormat('', $nights_src->field('list_data'), '%Y-%m-%d'),
                         self::NIGHTS_DELIMITER
                     ))
                     ->addFilter($nights_src->field('usr_id')->IN($this->pf->list_int_by_array($usr_ids)));
        $nights = $this->tf->DerivedTable($nights_space, 'nights');

        $participations = $this->tf->Table(self::HEAD_USERCOURSE_TABLE, 'usrcrs')
                                   ->addField($this->tf->field('usr_id'))
                                   ->addField($this->tf->field('crs_id'))
                                   ->addField($this->tf->field('booking_status'))
                                   ->addField($this->tf->field('participation_status'))
                                   ->addField($this->tf->field('booking_date'))
                                   ->addField($this->tf->field('ps_acquired_date'))
                                   ->addField($this->tf->field('idd_learning_time'))
                                   ->addField($this->tf->field('created_ts'))
                                   ->addField($this->tf->field('prior_night'))
                                   ->addField($this->tf->field('following_night'));

        $participations = $participations
            ->addConstraint(
                $participations->field('usr_id')->IN($this->pf->list_int_by_array($usr_ids))
            );

        $usr_data = $this->tf->Table(self::USR_DATA_TABLE, 'usr')
                             ->addField($this->tf->field('usr_id'))
                             ->addField($this->tf->field('firstname'))
                             ->addField($this->tf->field('lastname'))
                             ->addField($this->tf->field('email'))
                             ->addField($this->tf->field('login'));

        $space = $this->tf->TableSpace()
                          ->addTablePrimary($participations)
                          ->addTablePrimary($crs_data)
                          ->addTablePrimary($nights)
                          ->addTablePrimary($usr_data)
                          ->setRootTable($participations)
                          ->addDependency(
                              $this->tf->TableJoin(
                                  $participations,
                                  $crs_data,
                                  $participations->field('crs_id')->EQ($crs_data->field('crs_id'))
                              )
                          )
                          ->addDependency(
                              $this->tf->TableLeftJoin(
                                  $participations,
                                  $nights,
                                  $participations->field('crs_id')->EQ($nights->field('crs_id'))
                                                 ->_AND($participations->field('usr_id')
                                                                       ->EQ($nights->field('usr_id')))
                              )
                          )
                          ->addDependency(
                              $this->tf->TableJoin(
                                  $participations,
                                  $usr_data,
                                  $participations->field('usr_id')->EQ($usr_data->field('usr_id'))
                              )
                          );
        $space->request($crs_data->field('crs_id'))
              ->request($crs_data->field('title'))
              ->request($crs_data->field('crs_type'))
              ->request($crs_data->field('begin_date'))
              ->request($crs_data->field('end_date'))
              ->request($crs_data->field('idd_learning_time'), 'idd_crs')
              ->request($participations->field('usr_id'))
              ->request($participations->field('idd_learning_time'), 'idd_usr')
              ->request($participations->field('booking_status'))
              ->request($participations->field('participation_status'))
              ->request($participations->field('prior_night'))
              ->request($participations->field('following_night'))
              ->request($usr_data->field('firstname'))
              ->request($usr_data->field('lastname'))
              ->request($usr_data->field('login'))
              ->request($nights->field('nights'));
        return $space;
    }

    protected function extractDate($date)
    {
        if ($date === '0001-01-01') {
            return null;
        }
        return new \DateTime($date);
    }

    protected function formatIDDTime(int $minutes)
    {
        $reminder_minutes = $minutes % 60;
        $hours = ($minutes - $reminder_minutes) / 60;
        return str_pad((int) $hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad(
            (int) $reminder_minutes,
            2,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function extractOvernights($nights)
    {
        $nights = trim((string) $nights);
        if ($nights === '') {
            return [];
        }
        return array_map(function ($dt_string) {
            return new \DateTime(trim($dt_string));
        }, explode(self::NIGHTS_DELIMITER, $nights));
    }

    /**
     * @inheritdoc
     */
    public function getBookedByCourseTime(\DateTime $start, \DateTime $end, array $usr_ids) : array
    {
        foreach ($this->interpreter->interpret(
            $this->getBookedSpace($start, $end, $usr_ids)->query()
        ) as $row) {
            $return[] =
                new UserActivity(
                    UserActivity::ACT_TYPE_BOOKED,
                    (int) $row['usr_id'],
                    (int) $row['crs_id'],
                    $row['firstname'],
                    $row['lastname'],
                    $row['login'],
                    (string) $row['crs_type'],
                    $row['title'],
                    $this->extractDate($row['begin_date']),
                    $this->extractDate($row['end_date']),
                    $this->formatIDDTime((int) $row['idd_usr']),
                    $this->formatIDDTime((int) $row['idd_crs']),
                    $this->extractOvernights($row['nights']),
                    (bool) $row['prior_night'],
                    (bool) $row['following_night']
                );
        }
        return $return;
    }

    /**
     * Space containing all relevant data including "booked" constraints.
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int[]     $usr_ids
     * @return    TableRelations\TableSpace
     */
    protected function getBookedSpace(\DateTime $start, \DateTime $end, array $usr_ids)
    {
        $space = $this->getCommonSpace($usr_ids);
        $crs = $space->table('crs');
        $crs->addConstraint(
            $crs->field('begin_date')->GE()->str($start->format('Y-m-d'))
                ->_AND($crs->field('begin_date')->LE()->str($end->format('Y-m-d')))
        );
        $participations = $space->table('usrcrs');
        $participations->addConstraint(
            $participations->field('booking_status')->EQ()->str('participant')
        );
        return $space;
    }
}
