<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\ParticipationDocument;

class ilDB implements DB
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessfulCourseInformationsFor(
        int $usr_id,
        \DateTime $start,
        \DateTime $end
    ) : array {
        $str_start = $start->format("Y-m-d");
        $str_end = $end->format("Y-m-d");
        $default_date = "0001-01-01";

        $q = <<<SQL
SELECT
    IF(
        usrcrs.idd_learning_time IS NULL,
        crs.idd_learning_time,
        usrcrs.idd_learning_time
    ) AS idd_achieved,
    crs.title AS title,
    crs.crs_type AS type,
    IF(
        crs.begin_date IS NULL OR crs.begin_date = '$default_date',
        usrcrs.booking_date,
        crs.begin_date
    ) AS begin_date,
    IF(
        crs.end_date IS NULL OR crs.end_date = '$default_date',
        usrcrs.ps_acquired_date,
        crs.end_date
    ) AS end_date,
    crs.gti_category AS content,
    crs.provider AS provider
FROM
    hhd_usrcrs AS usrcrs
JOIN
    hhd_crs AS crs
ON
    (`usrcrs`.`crs_id` = `crs`.`crs_id`)
WHERE
    (
        (
            (
                (
                    (`crs`.`begin_date` IS NULL) OR(`crs`.`begin_date` = '$default_date')
                ) AND(
                    (
                        (
                            (
                                NOT(
                                    `usrcrs`.`ps_acquired_date` IS NULL
                                )
                            ) AND(
                                (
                                    `usrcrs`.`ps_acquired_date` < '$str_end'
                                ) OR(
                                    `usrcrs`.`ps_acquired_date` = '$str_end'
                                )
                            ) AND(
                                (
                                    '$str_start' < `usrcrs`.`ps_acquired_date`
                                ) OR(
                                    '$str_start' = `usrcrs`.`ps_acquired_date`
                                )
                            )
                        ) OR(
                            (
                                NOT(`usrcrs`.`booking_date` IS NULL)
                            ) AND(
                                `usrcrs`.`ps_acquired_date` IS NULL
                            ) AND(
                                (
                                    `usrcrs`.`booking_date` < '$str_end'
                                ) OR(
                                    `usrcrs`.`booking_date` = '$str_end'
                                )
                            ) AND(
                                (
                                    '$str_start' < `usrcrs`.`booking_date`
                                ) OR(
                                    '$str_start' = `usrcrs`.`booking_date`
                                )
                            )
                        )
                    )
                )
            ) OR(
                (
                    NOT(
                        (`crs`.`begin_date` IS NULL) OR(`crs`.`begin_date` = '$default_date')
                    )
                ) AND(
                    (
                        (NOT(`crs`.`end_date` IS NULL)) AND(
                            (`crs`.`end_date` < '$str_end') OR(`crs`.`end_date` = '$str_end')
                        ) AND(
                            ('$str_start' < `crs`.`end_date`) OR('$str_start' = `crs`.`end_date`)
                        )
                    ) OR(
                        (NOT(`crs`.`begin_date` IS NULL)) AND(`crs`.`end_date` IS NULL) AND(
                            (`crs`.`begin_date` < '$str_end') OR(`crs`.`begin_date` = '$str_end')
                        ) AND(
                            ('$str_start' < `crs`.`begin_date`) OR('$str_start' = `crs`.`begin_date`)
                        )
                    )
                )
            )
        )
    ) AND(
        (`usrcrs`.`usr_id` IN($usr_id)) AND(
            `usrcrs`.`booking_status` IN(
                'participant'
            )
        ) AND `usrcrs`.participation_status = 'successful'
    )
    ORDER BY begin_date
SQL;

        $res = $this->db->query($q);

        if ($this->db->numRows($res) == 0) {
            return [];
        }

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $begin_date = \DateTime::createFromFormat("Y-m-d", $row["begin_date"]);
            $end_date = \DateTime::createFromFormat("Y-m-d", $row["end_date"]);
            $ret[] = new Participation(
                (string) $row["title"],
                (string) $row["type"],
                $begin_date,
                $end_date,
                (string) $row["content"],
                (string) $row["provider"],
                (int) $row["idd_achieved"]
            );
        }

        return $ret;
    }
}
