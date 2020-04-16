<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\NotFinalized\CourseMember;

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
     * @inheritdoc
     */
    public function getNotFinalizedCourses(string $due_date) : array
    {
        $checkline = $this->db->quote($due_date, "text");
        $ret = [];
        $query = <<<EOT
SELECT
	object_reference.ref_id AS crs_ref_id,
	oref.ref_id AS child_ref_id,
	xcps_tpl_crs.crs_ref_id AS is_template,
	not_finalized_log.last_send AS last_send
FROM
	tree
JOIN object_reference ON tree.child = object_reference.ref_id
JOIN object_data ON object_reference.obj_id = object_data.obj_id
JOIN hhd_crs ON hhd_crs.crs_id = object_data.obj_id
JOIN tree t2 ON t2.path >= tree.path
	AND t2.path <= CONCAT(tree.path, ".Z")
JOIN object_reference oref ON oref.ref_id = t2.child
	AND oref.deleted IS NULL
JOIN object_data od ON od.obj_id = oref.obj_id
	AND od.type = "xcmb"
JOIN xcmb_settings ON xcmb_settings.obj_id = od.obj_id
LEFT JOIN xcps_tpl_crs ON xcps_tpl_crs.crs_ref_id = object_reference.ref_id
LEFT JOIN not_finalized_log ON not_finalized_log.child_ref_id = oref.ref_id
WHERE
	tree.path BETWEEN "1" AND "1.Z"
		AND tree.tree = 1
		AND object_data.type = "crs"
		AND object_reference.deleted IS NULL
		AND hhd_crs.end_date <= $checkline
		AND xcmb_settings.closed = 0
HAVING is_template IS NULL
ORDER BY object_reference.ref_id, child_ref_id, last_send DESC
EOT;
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $crs_ref_id = (int) $row["crs_ref_id"];
            $child_ref_id = (int) $row["child_ref_id"];
            $key = $crs_ref_id . "_" . $child_ref_id;

            if (!array_key_exists($key, $ret)) {
                $ret[$key] = false;
                if (is_null($row['last_send']) || new \DateTime($row['last_send']) < new \DateTime($due_date)) {
                    $ret[$key] = new NotFinalized(
                        $crs_ref_id,
                        $child_ref_id
                    );
                }
            }
        }

        return array_filter(
            $ret,
            function ($entry) {
                return $entry !== false;
            }
        );
    }
}
