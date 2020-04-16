<?php

/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\WorkflowReminder\MinMember;

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
    public function getCoursesWithoutMinMembers(int $offset) : array
    {
        $query = <<<EOT
SELECT
	object_reference.ref_id,
	oref.ref_id AS child_ref_id,
	hhd_crs.begin_date,
	xcps_tpl_crs.crs_ref_id AS is_template,
	xbkm_member.min,
	DATE_SUB(hhd_crs.begin_date, INTERVAL $offset DAY) AS checkdate
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
	AND od.type = "xbkm"
JOIN xbkm_member ON xbkm_member.obj_id = od.obj_id
LEFT JOIN xcps_tpl_crs ON xcps_tpl_crs.crs_ref_id = object_reference.ref_id
WHERE
	tree.path BETWEEN "1" AND "1.Z"
		AND tree.tree = 1
		AND object_data.type = "crs"
		AND object_reference.deleted IS NULL
HAVING is_template IS NULL AND checkdate = CURDATE()
ORDER BY object_reference.ref_id, xbkm_member.min
EOT;

        $res = $this->db->query($query);
        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $crs_ref_id = (int) $row["ref_id"];
            if (!array_key_exists($crs_ref_id, $ret)) {
                $data = new MinMember(
                    $crs_ref_id,
                    new \DateTime($row["begin_date"]),
                    (int) $row["child_ref_id"],
                    (int) $row["min"]
                );
                $ret[$crs_ref_id] = $data;
            }
        }

        return $ret;
    }
}
