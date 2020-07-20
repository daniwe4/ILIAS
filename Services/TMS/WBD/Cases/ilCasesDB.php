<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use ILIAS\TMS\WBD\Cases\DB;
use ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter;
use CaT\WBD\Cases;
use CaT\WBD\ErrorLog\DB as WBD_ERROR_LOG;
use ILIAS\TMS\WBD\Contact\DB as CONTACT_DB;
use CaT\WBD\Checks\WBDChecks;

class ilCasesDB implements DB
{
    use WBDChecks;

    const TABLE_USR_DATA = 'usr_data';
    const TABLE_UDF_TEXT = 'udf_text';
    const TABLE_WBD_ID = 'wbd_id';
    const TABLE_ANNOUNCE_WBD = 'announce_wbd';

    const TABLE_PARTICIPATIONS = 'hhd_usrcrs';
    const TABLE_COURSE = 'hhd_crs';
    const TABLE_WBD_CRS = 'hhd_wbd_crs';
    const TABLE_ERROR_LOG = 'wbd_request_errors';

    const FIELD_USR_ID = 'usr_id';
    const FIELD_ACTIVE = 'active';
    const FIELD_FIELD_ID = 'field_id';
    const FIELD_VALUE = 'value';
    const FIELD_ERROR_STATUS = "status";
    const FIELD_OPEN_ERRORS = "open_errors";

    const FIELD_CRS_ID = 'crs_id';
    const FIELD_PARTICIPATION_STATUS = 'participation_status';
    const FIELD_BOOKING_STATUS = 'booking_status';
    const FIELD_IDD_TIME = 'idd_learning_time';
    const FIELD_BOOKING_DATE = 'booking_date';
    const FIELD_FINISHED_DATE = 'ps_acquired_date';
    const FIELD_CRS_TITLE = 'title';
    const FIELD_BEGIN_DATE = 'begin_date';
    const FIELD_END_DATE = 'end_date';
    const FIELD_CRS_IDD_TIME = 'idd_learning_time';
    const FIELD_PROVIDER = 'provider';
    const FIELD_CRS_TYPE = 'wbd_learning_type';
    const FIELD_CRS_CONTENT = 'wbd_learning_content';
    const FIELD_INTERNAL_ID = 'internal_id';
    const FIELD_CONTACT_TITLE_TUTOR = 'contact_title_tutor';
    const FIELD_CONTACT_FIRSTNAME_TUTOR = 'contact_firstname_tutor';
    const FIELD_CONTACT_LASTNAME_TUTOR = 'contact_lastname_tutor';
    const FIELD_CONTACT_EMAIL_TUTOR = 'contact_email_tutor';
    const FIELD_CONTACT_PHONE_TUTOR = 'contact_phone_tutor';
    const FIELD_CONTACT_TITLE_ADMIN = 'contact_title_admin';
    const FIELD_CONTACT_FIRSTNAME_ADMIN = 'contact_firstname_admin';
    const FIELD_CONTACT_LASTNAME_ADMIN = 'contact_lastname_admin';
    const FIELD_CONTACT_EMAIL_ADMIN = 'contact_email_admin';
    const FIELD_CONTACT_PHONE_ADMIN = 'contact_phone_admin';
    const FIELD_CONTACT_TITLE_XCCL = 'contact_title_xccl';
    const FIELD_CONTACT_FIRSTNAME_XCCL = 'contact_firstname_xccl';
    const FIELD_CONTACT_LASTNAME_XCCL = 'contact_lastname_xccl';
    const FIELD_CONTACT_EMAIL_XCCL = 'contact_email_xccl';
    const FIELD_CONTACT_PHONE_XCCL = 'contact_phone_xccl';
    const FIELD_WBD_BOOKING_ID = 'wbd_booking_id';
    const FIELD_WBD_ID = 'wbd_id';
    const FIELD_CONTACT_TITLE = 'contact_title';
    const FIELD_CONTACT_FIRSTNAME = 'contact_firstname';
    const FIELD_CONTACT_LASTNAME = 'contact_lastname';
    const FIELD_CONTACT_EMAIL = 'contact_email';
    const FIELD_CONTACT_PHONE = 'contact_phone';

    const TP_BILDUNGSDIENSTLEISTER = 'Bildungsdienstleister';
    const TP_SERVICE = 'TP-Service';

    const DATE_REGEX = '#^20[0-9]{2}\\-[0-9]{2}\\-[0-9]{2}$#';

    protected static $tp_pool_for_request = [
        self::TP_SERVICE
    ];

    protected static $tp_pool_for_report = [
        self::TP_BILDUNGSDIENSTLEISTER,
        self::TP_SERVICE
    ];

    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var TableRelations\SQLQueryInterpreter
     */
    protected $sql_q_i;

    /**
     * @var TableRelations\TableFactory
     */
    protected $tab_f;

    /**
     * @var Filter\PredicateFactory
     */
    protected $pre_f;

    /**
     * @var Filter\TypeFactory
     */
    protected $typ_f;

    /**
     * @var WBD_ERROR_LOG
     */
    protected $wbd_error_log;

    /**
     * @var CONTACT_DB
     */
    protected $contact_db;

    /**
     * @var string|null
     */
    protected $etr_contact_mode;

    /**
     * @var string[]|null
     */
    protected $etr_contact_static_info;

    public function __construct(
        \ilDBInterface $db,
        TableRelations\SQLQueryInterpreter $sql_q_i,
        TableRelations\TableFactory $tab_f,
        Filter\PredicateFactory $pre_f,
        Filter\TypeFactory $typ_f,
        WBD_ERROR_LOG $wbd_error_log,
        CONTACT_DB $contact_db
    ) {
        $this->db = $db;
        $this->sql_q_i = $sql_q_i;
        $this->tab_f = $tab_f;
        $this->pre_f = $pre_f;
        $this->typ_f = $typ_f;
        $this->wbd_error_log = $wbd_error_log;
        $this->contact_db = $contact_db;

        $this->etr_contact_mode = null;
        $this->etr_contact_static_info = null;
    }

    /**
     * @inheritDoc
     */
    public function getParticipationsToReport(
        int $gutberaten_udf_id,
        int $announce_wbd_id,
        \DateTime $start_date = null
    ) : array {
        $space = $this->participationsSpace($gutberaten_udf_id, $announce_wbd_id);
        $error_status = $space->table(self::TABLE_ERROR_LOG)->field(self::FIELD_OPEN_ERRORS);
        $finish_date = $space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_FINISHED_DATE);

        $space
            ->addFilter(
                $this->pre_f->_ANY(
                    $error_status->EQ($this->pre_f->int(0)),
                    $error_status->IS_NULL()
                )
            )
        ;

        if (!is_null($start_date)) {
            $space
                ->addFilter(
                    $finish_date->GT($this->pre_f->str($start_date->format("Y-m-d")))
                )
            ;
        }

        $contact_mode = $this->getEduTrackingContactMode();
        $contact_info_static = $this->contact_db->eduTrackingStaticContactInfo();

        $space
            ->request($space->table(self::TABLE_WBD_ID)->field(self::FIELD_VALUE), self::FIELD_WBD_ID)
            ->request($space->table(self::TABLE_WBD_CRS)->field(self::FIELD_INTERNAL_ID))
            ->request($space->table(self::TABLE_COURSE)->field(self::FIELD_CRS_TITLE))
            ->request($space->table(self::TABLE_COURSE)->field(self::FIELD_PROVIDER))
            ->request($space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CRS_TYPE))
            ->request($space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CRS_CONTENT))
            ->request(
                $this->contactFieldPhoneAccordingToEBRFor($space, $contact_mode, $contact_info_static),
                self::FIELD_CONTACT_PHONE
            )
            ->request(
                $this->contactFieldEmailAccordingToEBRFor($space, $contact_mode, $contact_info_static),
                self::FIELD_CONTACT_EMAIL
            )
            ->request(
                $this->contactFieldTitleAccordingToEBRFor($space, $contact_mode, $contact_info_static),
                self::FIELD_CONTACT_TITLE
            )
            ->request(
                $this->contactFieldFirstnameAccordingToEBRFor($space, $contact_mode, $contact_info_static),
                self::FIELD_CONTACT_FIRSTNAME
            )
            ->request(
                $this->contactFieldLastnameAccordingToEBRFor($space, $contact_mode, $contact_info_static),
                self::FIELD_CONTACT_LASTNAME
            )
            ->request(
                $this->tab_f->ifThenElse(
                    self::FIELD_IDD_TIME,
                    $space
                        ->table(self::TABLE_PARTICIPATIONS)
                        ->field(self::FIELD_IDD_TIME)
                        ->IS_NULL()->_NOT(),
                    $space
                        ->table(self::TABLE_PARTICIPATIONS)
                        ->field(self::FIELD_IDD_TIME),
                    $space
                        ->table(self::TABLE_COURSE)
                        ->field(self::FIELD_CRS_IDD_TIME)
                )
            )
            ->request($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_USR_ID))
            ->request($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_CRS_ID))
            ->request($space->table(self::TABLE_COURSE)->field(self::FIELD_BEGIN_DATE))
            ->request($space->table(self::TABLE_COURSE)->field(self::FIELD_END_DATE))
            ->request($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_BOOKING_DATE))
            ->request($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_FINISHED_DATE))
            ->request($space->table(self::TABLE_ERROR_LOG)->field(self::FIELD_OPEN_ERRORS))
            ->groupBy($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_CRS_ID))
            ->groupBy($space->table(self::TABLE_PARTICIPATIONS)->field(self::FIELD_USR_ID))
        ;

        $ret = [];
        foreach ($this->sql_q_i->interpret($space->query()) as $row) {
            list($begin_date, $end_date) =
                $this->extractCourseDates(
                    $row[self::FIELD_BEGIN_DATE],
                    $row[self::FIELD_BOOKING_DATE],
                    $row[self::FIELD_END_DATE],
                    $row[self::FIELD_FINISHED_DATE]
                );

            $row[self::FIELD_BEGIN_DATE] = $begin_date;
            $row[self::FIELD_END_DATE] = $end_date;
            try {
                $ret[] = $this->participationByRow($row);
            } catch (\InvalidArgumentException $e) {
                $this->logInvalidArgument($row, $e->getMessage());
            }
        }

        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function getIdsForParticipationRequest(int $gutberaten_udf_id, int $announce_wbd_id) : array
    {
        $space = $this->userSpace($gutberaten_udf_id, $announce_wbd_id, self::$tp_pool_for_request);
        $space->request($space->table(self::TABLE_USR_DATA)->field(self::FIELD_USR_ID))
              ->request($space->table(self::TABLE_WBD_ID)->field(self::FIELD_VALUE), self::FIELD_WBD_ID);
        $usr_data = $space->table(self::TABLE_USR_DATA);
        $usr_data->addConstraint(
            $usr_data->field(self::FIELD_ACTIVE)->EQ($this->pre_f->int(1))
        );

        $ret = [];
        $res = $this->db->query($this->sql_q_i->getSql($space->query()));
        while ($row = $this->db->fetchAssoc($res)) {
            $usr_id = (int) $row[self::FIELD_USR_ID];
            $wbd_id = (string) $row[self::FIELD_WBD_ID];
            $ret[] = new Cases\RequestParticipations($usr_id, $wbd_id);
        }

        return $ret;
    }

    protected function userSpace(
        int $gutberaten_udf_id,
        int $announce_wbd_id,
        array $tp_types = []
    ) : TableRelations\Tables\TableSpace {
        $usr_data = $this->tab_f->Table(self::TABLE_USR_DATA, self::TABLE_USR_DATA)
                                ->addField($this->tab_f->field(self::FIELD_USR_ID))
                                ->addField($this->tab_f->field(self::FIELD_ACTIVE));

        $wbd_id = $this->tab_f->Table(self::TABLE_UDF_TEXT, self::TABLE_WBD_ID)
                              ->addField($this->tab_f->field(self::FIELD_USR_ID))
                              ->addField($this->tab_f->field(self::FIELD_FIELD_ID))
                              ->addField($this->tab_f->field(self::FIELD_VALUE));
        $wbd_id->addConstraint(
            $this->pre_f->_ALL(
                $wbd_id->field(self::FIELD_FIELD_ID)->EQ($this->pre_f->int($gutberaten_udf_id)),
                $wbd_id->field(self::FIELD_VALUE)->NEQ($this->pre_f->str('')),
                $wbd_id->field(self::FIELD_VALUE)->IS_NULL()->_NOT()
            )
        );
        $announce_wbd = $this->tab_f->Table(self::TABLE_UDF_TEXT, self::TABLE_ANNOUNCE_WBD)
                                    ->addField($this->tab_f->field(self::FIELD_USR_ID))
                                    ->addField($this->tab_f->field(self::FIELD_FIELD_ID))
                                    ->addField($this->tab_f->field(self::FIELD_VALUE));

        if (count($tp_types) > 0) {
            $announce_wbd->addConstraint(
                $this->pre_f->_ALL(
                    $announce_wbd->field(self::FIELD_FIELD_ID)->EQ($this->pre_f->int($announce_wbd_id)),
                    $announce_wbd->field(self::FIELD_VALUE)->IN($this->pre_f->list_string_by_array($tp_types))
                )
            );
        } else {
            $announce_wbd->addConstraint(
                $this->pre_f->_ALL(
                    $announce_wbd->field(self::FIELD_FIELD_ID)->EQ($this->pre_f->int($announce_wbd_id))
                )
            );
        }


        $user_space = $this->tab_f->TableSpace()
                                  ->addTablePrimary($usr_data)
                                  ->addTablePrimary($wbd_id)
                                  ->addTablePrimary($announce_wbd)
                                  ->setRootTable($usr_data)
                                  ->addDependency(
                                      $this->tab_f->TableJoin(
                                          $usr_data,
                                          $wbd_id,
                                          $usr_data->field(self::FIELD_USR_ID)->EQ($wbd_id->field(self::FIELD_USR_ID))
                                      )
                                  )
                                  ->addDependency(
                                      $this->tab_f->TableJoin(
                                          $usr_data,
                                          $announce_wbd,
                                          $usr_data->field(self::FIELD_USR_ID)->EQ($announce_wbd->field(self::FIELD_USR_ID))
                                      )
                                  );
        return $user_space;
    }

    public function getParticipationsToCancel() : array
    {
        $q = "SELECT wbd.crs_id, wbd.usr_id, wbd.gutberaten_id, wbd.wbd_booking_id," . PHP_EOL
            . " wbd.title, wbd.minutes" . PHP_EOL
            . " FROM xwbd_report_crs_values wbd " . PHP_EOL
            . " JOIN hhd_usrcrs husrcrs ON husrcrs.crs_id = wbd.crs_id" . PHP_EOL
            . "     AND husrcrs.wbd_booking_id = wbd.wbd_booking_id" . PHP_EOL
            . " JOIN hhd_crs hcrs ON hcrs.crs_id = wbd.crs_id" . PHP_EOL
            . "     AND husrcrs.usr_id = wbd.usr_id" . PHP_EOL
            . " LEFT JOIN wbd_request_errors err ON err.usr_id = husrcrs.usr_id" . PHP_EOL
            . "     AND err.crs_id = husrcrs.crs_id" . PHP_EOL
            . " WHERE (" . PHP_EOL
            . "     hcrs.title != wbd.title " . PHP_EOL
            . "         OR IF(husrcrs.idd_learning_time IS NOT NULL, husrcrs.idd_learning_time, hcrs.idd_learning_time) != wbd.minutes" . PHP_EOL
            . "         OR IF((hcrs.begin_date IS NOT NULL AND hcrs.begin_date != '0001-01-01'), hcrs.begin_date, husrcrs.booking_date) != wbd.begin_date" . PHP_EOL
            . "         OR IF((hcrs.end_date IS NOT NULL AND hcrs.end_date != '0001-01-01'), hcrs.end_date, husrcrs.ps_acquired_date) != wbd.end_date" . PHP_EOL
            . "         OR husrcrs.booking_status IN ('cancelled', 'cancelled_after_deadline')" . PHP_EOL
            . " ) AND" . PHP_EOL
            ."     0 = (SELECT count(err.status)".PHP_EOL
            ."              FROM wbd_request_errors err".PHP_EOL
            ."              WHERE err.usr_id = husrcrs.usr_id".PHP_EOL
            ."                  AND err.crs_id = husrcrs.crs_id".PHP_EOL
            ."                  AND err.status IN ('open', 'not_resolvable')".PHP_EOL
            ."         )"
        ;

        $res = $this->db->query($q);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = new Cases\CancelParticipation(
                (int) $row['crs_id'],
                (int) $row['usr_id'],
                (string) $row['title'],
                (int) $row['minutes'],
                $row['wbd_booking_id'],
                $row['gutberaten_id']
            );
        }
        return $ret;
    }

    protected function participationsSpace(int $gutberaten_udf_id, int $announce_wbd_id) : TableRelations\Tables\TableSpace
    {
        $participations = $this->tab_f->Table(
            self::TABLE_PARTICIPATIONS,
            self::TABLE_PARTICIPATIONS
        )
                                      ->addField($this->tab_f->field(self::FIELD_USR_ID))
                                      ->addField($this->tab_f->field(self::FIELD_CRS_ID))
                                      ->addField($this->tab_f->field(self::FIELD_PARTICIPATION_STATUS))
                                      ->addField($this->tab_f->field(self::FIELD_BOOKING_STATUS))
                                      ->addField($this->tab_f->field(self::FIELD_IDD_TIME))
                                      ->addField($this->tab_f->field(self::FIELD_BOOKING_DATE))
                                      ->addField($this->tab_f->field(self::FIELD_WBD_BOOKING_ID))
                                      ->addField($this->tab_f->field(self::FIELD_FINISHED_DATE));
        $participations->addConstraint($this->pre_f->_ALL(
            $participations->field(self::FIELD_BOOKING_STATUS)->EQ($this->pre_f->str('participant')),
            $participations->field(self::FIELD_PARTICIPATION_STATUS)->EQ($this->pre_f->str('successful')),
            $participations->field(self::FIELD_WBD_BOOKING_ID)->IS_NULL()
        ));
        $courses = $this->tab_f->Table(
            self::TABLE_COURSE,
            self::TABLE_COURSE
        )
                               ->addField($this->tab_f->field(self::FIELD_CRS_TITLE))
                               ->addField($this->tab_f->field(self::FIELD_BEGIN_DATE))
                               ->addField($this->tab_f->field(self::FIELD_END_DATE))
                               ->addField($this->tab_f->field(self::FIELD_CRS_ID))
                               ->addField($this->tab_f->field(self::FIELD_CRS_IDD_TIME))
                               ->addField($this->tab_f->field(self::FIELD_PROVIDER));
        $wbd_crs = $this->tab_f->Table(
            self::TABLE_WBD_CRS,
            self::TABLE_WBD_CRS
        )
                               ->addField($this->tab_f->field(self::FIELD_CRS_ID))
                               ->addField($this->tab_f->field(self::FIELD_CRS_TYPE))
                               ->addField($this->tab_f->field(self::FIELD_CRS_CONTENT))
                               ->addField($this->tab_f->field(self::FIELD_INTERNAL_ID))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_TITLE_TUTOR))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_FIRSTNAME_TUTOR))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_LASTNAME_TUTOR))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_EMAIL_TUTOR))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_PHONE_TUTOR))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_TITLE_ADMIN))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_FIRSTNAME_ADMIN))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_LASTNAME_ADMIN))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_EMAIL_ADMIN))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_PHONE_ADMIN))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_TITLE_XCCL))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_FIRSTNAME_XCCL))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_LASTNAME_XCCL))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_EMAIL_XCCL))
                               ->addField($this->tab_f->field(self::FIELD_CONTACT_PHONE_XCCL));
        $wbd_crs->addConstraint(
            $this->pre_f->_ALL(
                $wbd_crs->field(self::FIELD_CRS_CONTENT)->IN($this->pre_f->list_string_by_array(self::$topics)),
                $wbd_crs->field(self::FIELD_CRS_TYPE)->IN($this->pre_f->list_string_by_array(self::$types))
            )
        );

        $open_errors_src = $this->tab_f->Table(self::TABLE_ERROR_LOG, 'open_error_src')
                                       ->addField($this->tab_f->field(self::FIELD_CRS_ID))
                                       ->addField($this->tab_f->field(self::FIELD_USR_ID))
                                       ->addField($this->tab_f->field(self::FIELD_ERROR_STATUS))
        ;

        $open_errors_space = $this->tab_f->TableSpace()
                                         ->addTablePrimary($open_errors_src)
                                         ->setRootTable($open_errors_src)
                                         ->groupBy($open_errors_src->field(self::FIELD_USR_ID))
                                         ->groupBy($open_errors_src->field(self::FIELD_CRS_ID))
        ;

        $open_errors_count_field =
            $this->tab_f->Sum(
                '',
                $this->tab_f->IfThenElse(
                    '',
                    $open_errors_src->field(self::FIELD_ERROR_STATUS)->IN(
                        $this->pre_f->list_string_by_array(["open", "not_resolvable"])
                    ),
                    $this->tab_f->ConstInt('', 1),
                    $this->tab_f->ConstInt('', 0)
                )
            )
        ;

        $open_errors_space->request($open_errors_src->field(self::FIELD_CRS_ID))
                          ->request($open_errors_src->field(self::FIELD_USR_ID))
                          ->request($open_errors_count_field, self::FIELD_OPEN_ERRORS)
        ;

        $open_errors = $this->tab_f->DerivedTable($open_errors_space, self::TABLE_ERROR_LOG);

        $participation_space = $this->userSpace(
            $gutberaten_udf_id,
            $announce_wbd_id,
            self::$tp_pool_for_report
        );
        $participation_space
            ->addTablePrimary($participations)
            ->addTablePrimary($courses)
            ->addTablePrimary($wbd_crs)
            ->addTableSecondary($open_errors)
            ->addDependency(
                $this->tab_f->TableJoin(
                    $participation_space->table(self::TABLE_USR_DATA),
                    $participations,
                    $participation_space->table(self::TABLE_USR_DATA)->field(self::FIELD_USR_ID)->EQ($participations->field(self::FIELD_USR_ID))
                )
            )
            ->addDependency(
                $this->tab_f->TableJoin(
                    $participations,
                    $courses,
                    $participations->field(self::FIELD_CRS_ID)->EQ($courses->field(self::FIELD_CRS_ID))
                )
            )
            ->addDependency(
                $this->tab_f->TableJoin(
                    $participations,
                    $wbd_crs,
                    $participations->field(self::FIELD_CRS_ID)->EQ($wbd_crs->field(self::FIELD_CRS_ID))
                )
            )
            ->addDependency(
                $this->tab_f->TableLeftJoin(
                    $participations,
                    $open_errors,
                    $this->pre_f->_ALL(
                        $participations->field(self::FIELD_CRS_ID)->EQ($open_errors->field(self::FIELD_CRS_ID)),
                        $participations->field(self::FIELD_USR_ID)->EQ($open_errors->field(self::FIELD_USR_ID))
                    )
                )
            )
            ->addFilter(
                $this->pre_f->_ANY(
                    $this->pre_f->_ALL(
                        $participations->field(self::FIELD_IDD_TIME)->GT($this->pre_f->int(0)),
                        $participations->field(self::FIELD_IDD_TIME)->IS_NULL()->_NOT()
                    ),
                    $this->pre_f->_ALL(
                        $participations->field(self::FIELD_IDD_TIME)->EQ($this->pre_f->int(0))->_OR($participations->field(self::FIELD_IDD_TIME)->IS_NULL()),
                        $courses->field(self::FIELD_CRS_IDD_TIME)->GT($this->pre_f->int(0)),
                        $courses->field(self::FIELD_CRS_IDD_TIME)->IS_NULL()->_NOT()
                    )
                )
            );
        return $participation_space;
    }

    protected function extractCourseDates(
        string $begin_date,
        string $booking_date,
        string $end_date,
        string $finished_date
    ) : array {
        $r_begin_date = null;
        $r_end_date = null;
        if (preg_match(self::DATE_REGEX, (string) $begin_date)) {
            $r_begin_date = $begin_date;
            if (preg_match(self::DATE_REGEX, (string) $end_date)) {
                $r_end_date = $end_date;
            } else {
                $r_end_date = $begin_date;
            }
        } elseif (preg_match(self::DATE_REGEX, (string) $booking_date)) {
            $r_begin_date = $booking_date;
            if (preg_match(self::DATE_REGEX, (string) $finished_date)) {
                $r_end_date = $finished_date;
            } else {
                $r_end_date = $booking_date;
            }
        }
        return [$r_begin_date,$r_end_date];
    }

    protected function logInvalidArgument(array $row, string $message)
    {
        $usr_id = (int) $row[self::FIELD_USR_ID];
        $infos = \ilObjUser::_lookupName($usr_id);
        $this->wbd_error_log->store(
            $usr_id,
            $row[self::TABLE_WBD_ID],
            (int) $row[self::FIELD_CRS_ID],
            (string) $row[self::FIELD_CRS_TITLE],
            (int) $row[self::FIELD_IDD_TIME],
            $message
        );
    }

    protected function participationByRow(array $row) : Cases\ReportParticipation
    {
        return new Cases\ReportParticipation(
            (int) $row[self::FIELD_CRS_ID],
            (int) $row[self::FIELD_USR_ID],
            (string) $row[self::FIELD_WBD_ID],
            (string) $row[self::FIELD_CRS_TITLE],
            (int) $row[self::FIELD_IDD_TIME],
            \DateTime::createFromFormat('Y-m-d', $row[self::FIELD_BEGIN_DATE]),
            \DateTime::createFromFormat('Y-m-d', $row[self::FIELD_END_DATE]),
            (string) $row[self::FIELD_CRS_TYPE],
            (string) $row[self::FIELD_CRS_CONTENT],
            $this->buildInternalIdByRow($row),
            (string) $row[self::FIELD_CONTACT_TITLE],
            (string) $row[self::FIELD_CONTACT_FIRSTNAME],
            (string) $row[self::FIELD_CONTACT_LASTNAME],
            (string) $row[self::FIELD_CONTACT_PHONE],
            (string) $row[self::FIELD_CONTACT_EMAIL]
        );
    }

    protected function buildInternalIdByRow(array $row) : string
    {
        if (trim((string) $row[self::FIELD_INTERNAL_ID]) === '') {
            return '';
        }
        if (strpos($row[self::FIELD_INTERNAL_ID], '{USR_ID}') === false) {
            throw new Exception('Invalid internal id template. Missing placeholder. ' . $row[self::FIELD_CRS_ID]);
        }
        return str_replace('{USR_ID}', $row[self::FIELD_USR_ID], $row[self::FIELD_INTERNAL_ID]);
    }

    protected function contactFieldPhoneAccordingToEBRFor($space, string $contact_mode, array $static_contact_data)
    {
        switch ($contact_mode) {
            case CONTACT_DB::ETR_CONTACT_MODE_NONE:
                return $this->tab_f->constString('');
            case CONTACT_DB::ETR_CONTACT_MODE_STATIC:
                return $this->getContactFor(CONTACT_DB::CONTACT_PHONE, $static_contact_data);
            case CONTACT_DB::ETR_CONTACT_MODE_TUTOR:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_PHONE_TUTOR);
            case CONTACT_DB::ETR_CONTACT_MODE_ADMIN:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_PHONE_ADMIN);
            case CONTACT_DB::ETR_CONTACT_MODE_CCL:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_PHONE_XCCL);
        }
    }
    protected function contactFieldEmailAccordingToEBRFor($space, string $contact_mode, array $static_contact_data)
    {
        switch ($contact_mode) {
            case CONTACT_DB::ETR_CONTACT_MODE_NONE:
                return $this->tab_f->constString('');
            case CONTACT_DB::ETR_CONTACT_MODE_STATIC:
                return $this->getContactFor(CONTACT_DB::CONTACT_EMAIL, $static_contact_data);
            case CONTACT_DB::ETR_CONTACT_MODE_TUTOR:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_EMAIL_TUTOR);
            case CONTACT_DB::ETR_CONTACT_MODE_ADMIN:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_EMAIL_ADMIN);
            case CONTACT_DB::ETR_CONTACT_MODE_CCL:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_EMAIL_XCCL);
        }
    }
    protected function contactFieldTitleAccordingToEBRFor($space, string $contact_mode, array $static_contact_data)
    {
        switch ($contact_mode) {
            case CONTACT_DB::ETR_CONTACT_MODE_NONE:
                return $this->tab_f->constString('');
            case CONTACT_DB::ETR_CONTACT_MODE_STATIC:
                return $this->getContactFor(CONTACT_DB::CONTACT_TITLE, $static_contact_data);
            case CONTACT_DB::ETR_CONTACT_MODE_TUTOR:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_TITLE_TUTOR);
            case CONTACT_DB::ETR_CONTACT_MODE_ADMIN:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_TITLE_ADMIN);
            case CONTACT_DB::ETR_CONTACT_MODE_CCL:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_TITLE_XCCL);
        }
    }
    protected function contactFieldFirstnameAccordingToEBRFor($space, string $contact_mode, array $static_contact_data)
    {
        switch ($contact_mode) {
            case CONTACT_DB::ETR_CONTACT_MODE_NONE:
                return $this->tab_f->constString('');
            case CONTACT_DB::ETR_CONTACT_MODE_STATIC:
                return $this->getContactFor(CONTACT_DB::CONTACT_FIRSTNAME, $static_contact_data);
            case CONTACT_DB::ETR_CONTACT_MODE_TUTOR:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_FIRSTNAME_TUTOR);
            case CONTACT_DB::ETR_CONTACT_MODE_ADMIN:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_FIRSTNAME_ADMIN);
            case CONTACT_DB::ETR_CONTACT_MODE_CCL:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_FIRSTNAME_XCCL);
        }
    }
    protected function contactFieldLastnameAccordingToEBRFor($space, string $contact_mode, array $static_contact_data)
    {
        switch ($contact_mode) {
            case CONTACT_DB::ETR_CONTACT_MODE_NONE:
                return $this->tab_f->constString('');
            case CONTACT_DB::ETR_CONTACT_MODE_STATIC:
                return $this->getContactFor(CONTACT_DB::CONTACT_LASTNAME, $static_contact_data);
            case CONTACT_DB::ETR_CONTACT_MODE_TUTOR:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_LASTNAME_TUTOR);
            case CONTACT_DB::ETR_CONTACT_MODE_ADMIN:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_LASTNAME_ADMIN);
            case CONTACT_DB::ETR_CONTACT_MODE_CCL:
                return $space->table(self::TABLE_WBD_CRS)->field(self::FIELD_CONTACT_LASTNAME_XCCL);
        }
    }

    protected function getContactFor(string $key, $values) : ILIAS\TMS\TableRelations\Tables\DerivedFields\ConstString
    {
        $result = '';
        if (
            array_key_exists($key, $values) &&
            ! is_null($values[$key])
        ) {
            $result = $values[$key];
        }

        return $this->tab_f->constString('', $result);
    }

    protected function getEduTrackingContactMode() : string
    {
        if (is_null($this->etr_contact_mode)) {
            $this->etr_contact_mode = $this->contact_db->eduTrackingContactMode();
        }
        return $this->etr_contact_mode;
    }

    protected function getEduTrackingStaticContactInfo() : array
    {
        if (is_null($this->etr_contact_static_info)) {
            $this->etr_contact_static_info = $this->contact_db->eduTrackingStaticContactInfo();
        }
        return $this->etr_contact_static_info;
    }
}
