<?php

declare(strict_types=1);

namespace CaT\Plugins\EduBiography\Config\OverviewCertificate\Certificate;

class ilUserSpecificValues
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function getIDDTimesFor(
        int $usr_id,
        \DateTime $start,
        \DateTime $end
    ) {
        $str_start = $start->format("Y-m-d");
        $str_end = $end->format("Y-m-d");

        $q = <<<SQL
SELECT
    SUM(
        IF(
            usrcrs.participation_status = 'successful',
            IF(
                usrcrs.idd_learning_time IS NULL,
                crs.idd_learning_time,
                usrcrs.idd_learning_time
            ),
            0
        )
    ) AS sum_idd_achieved
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
                    (`crs`.`begin_date` IS NULL) OR(`crs`.`begin_date` = '0001-01-01')
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
                        (`crs`.`begin_date` IS NULL) OR(`crs`.`begin_date` = '0001-01-01')
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
                'participant',
                'waiting',
                'approval_pending'
            )
        )
    )
GROUP BY
    usrcrs.usr_id
SQL;


        $res = $this->db->query($q);

        if ($this->db->numRows($res) == 0) {
            return 0;
        }

        $row = $this->db->fetchAssoc($res);
        return (int) $row["sum_idd_achieved"];
    }
}
