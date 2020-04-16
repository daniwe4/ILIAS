<?php declare(strict_types=1);


class ResetBookingDatesToFirstValue
{
    const ROW_ID = 'row_id';
    const BOOKING_DATE = 'booking_date';
    const PS_ACQUIRED_DATE = 'ps_acquired_date';

    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }


    /**
     * Search all head entries, which have a booking date after $date.
     * Get the history. Find the first booking -/ participation status set- date.
     * Set all successive entries to these dates.
     */
    public function resetAfter(\DateTime $date, bool $reset_tail = false)
    {
        $dt_string = $date->format('Y-m-d');
        $q = 'SELECT crs_id, usr_id'
            . '	FROM hhd_usrcrs'
            . '	JOIN object_data ON crs_id = obj_id AND type = ' . $this->db->quote('crs', 'text')
            . '	WHERE booking_date IS NOT NULL'
            . '		AND booking_date >= ' . $this->db->quote($dt_string, 'text');
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            $this->dealWithCase((int) $rec['crs_id'], (int) $rec['usr_id'], $reset_tail);
        }
    }

    protected function dealWithCase(int $crs_id, int $usr_id, bool $reset_tail)
    {
        $reference_booking_date = null;
        $reference_ps_acquired_date = null;
        foreach ($this->getHistoricEntriesForCase($crs_id, $usr_id) as $data_set) {
            if ($reference_booking_date === null) {
                if (trim((string) $data_set['booking_date']) !== '') {
                    $reference_booking_date = $data_set['booking_date'];
                    $booking_ref_row = (int) $data_set['row_id'];
                }
            }
            if ($reference_ps_acquired_date === null) {
                if (trim((string) $data_set['ps_acquired_date']) !== '') {
                    $reference_ps_acquired_date = $data_set['ps_acquired_date'];
                    $ps_acquired_ref_row = (int) $data_set['row_id'];
                }
            }
            if ($reference_ps_acquired_date !== null && $reference_booking_date !== null) {
                break;
            }
        }
        if ($reference_ps_acquired_date === null && $reference_booking_date === null) {
            return;
        }
        $this->resetHead(
            $crs_id,
            $usr_id,
            $reference_booking_date,
            $reference_ps_acquired_date
        );
        if ($reset_tail) {
            $this->resetTail(
                $crs_id,
                $usr_id,
                $reference_booking_date,
                $booking_ref_row,
                $reference_ps_acquired_date,
                $ps_acquired_ref_row
            );
        }
    }


    protected function resetHead(
        int $crs_id,
        int $usr_id,
        string $booking_date,
        $ps_acquired_date
    ) {
        $update_head = 'UPDATE hhd_usrcrs'
                        . '	SET'
                        . '	booking_date = ' . $this->db->quote($booking_date, 'text');
        if (trim((string) $ps_acquired_date) !== '') {
            $update_head .= ',	ps_acquired_date = ' . $this->db->quote($ps_acquired_date, 'text');
        } else {
            $update_head .= ',	ps_acquired_date = NULL';
        }
        $update_head .= '	WHERE crs_id = ' . $this->db->quote($crs_id, 'integer')
                        . '		AND usr_id = ' . $this->db->quote($usr_id, 'integer');
        $this->db->manipulate($update_head);
    }

    protected function resetTail(
        int $crs_id,
        int $usr_id,
        string $booking_date,
        int $booking_ref_row,
        $ps_acquired_date,
        $ps_acquired_ref_row
    ) {
        $update_tail_booking =
                        'UPDATE hst_usrcrs'
                        . '	SET'
                        . '	booking_date = ' . $this->db->quote($booking_date, 'text')
                        . '	WHERE crs_id = ' . $this->db->quote($crs_id, 'integer')
                        . '		AND usr_id = ' . $this->db->quote($usr_id, 'integer')
                        . '		AND row_id > ' . $this->db->quote($booking_ref_row, 'integer');
        $this->db->manipulate($update_tail_booking);
        if (trim((string) $ps_acquired_date) === '') {
            return;
        }
        $update_tail_ps_acquired =
                        'UPDATE hst_usrcrs'
                        . '	SET'
                        . '	ps_acquired_date = ' . $this->db->quote($ps_acquired_date, 'text')
                        . '	WHERE crs_id = ' . $this->db->quote($crs_id, 'integer')
                        . '		AND usr_id = ' . $this->db->quote($usr_id, 'integer')
                        . '		AND row_id > ' . $this->db->quote($ps_acquired_ref_row, 'integer');
        $this->db->manipulate($update_tail_ps_acquired);
    }

    protected function getHistoricEntriesForCase(int $crs_id, int $usr_id) : array
    {
        $q = 'SELECT'
            . '	crs_id'
            . '	,usr_id'
            . '	,booking_date'
            . '	,ps_acquired_date'
            . '	,row_id'
            . '	FROM hst_usrcrs'
            . '	WHERE crs_id = ' . $this->db->quote($crs_id, 'integer')
            . '		AND usr_id = ' . $this->db->quote($usr_id, 'integer')
            . '	ORDER BY row_id ASC';
        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[] = $rec;
        }
        return $return;
    }
}
