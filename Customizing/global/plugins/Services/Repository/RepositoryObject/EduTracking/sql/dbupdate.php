<#1>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilDB($ilDB);
$wbd_db->createTable();
?>

<#2>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilDB($ilDB);
$wbd_db->createPrimaryKey();
?>

<#3>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\Configuration\ilDB($ilDB);
$wbd_db->createSequence();
?>

<#4>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilDB($ilDB);
$wbd_db->createTable();
?>

<#5>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilDB($ilDB, $app_event_handler);
$wbd_db->createPrimaryKey();
?>

<#6>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\IDD\Configuration\ilDB($ilDB, $app_event_handler);
$wbd_db->createSequence();
?>

<#7>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($ilDB);
$wbd_db->createTable();
?>

<#8>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($ilDB);
$wbd_db->createPrimaryKey();
?>

<#9>
<?php

$wbd_db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($ilDB);
$wbd_db->createSequence();
?>

<#10>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\ilDB($ilDB, $app_event_handler);
$wbd_db->createTable();
?>

<#11>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\ilDB($ilDB, $app_event_handler);
$wbd_db->createPrimaryKey();
?>

<#12>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\IDD\ilDB($ilDB, $app_event_handler);
$wbd_db->createTable();
?>

<#13>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\IDD\ilDB($ilDB, $app_event_handler);
$wbd_db->createPrimaryKey();
?>

<#14>
<?php
;
?>

<#15>
<?php
;
?>

<#16>
<?php
$plug = 'EduTracking';
$plug_id = 'xetr';
$class_name = CaT\Plugins\EduTracking\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$class_name_sql'";
$ilDB->query($query);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjEduTracking();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#17>
<?php

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/EduTracking/classes/class.ilObjEduTracking.php");
global $DIC;
$db = $DIC['ilDB'];
$app_event_handler = $DIC['ilAppEventHandler'];
$relevant_objects_query = 'SELECT ref_id'
                            . '	FROM xetr_idd_data'
                            . '	JOIN object_reference USING(obj_id)'
                            . '	WHERE deleted IS NULL';
$res = $db->query($relevant_objects_query);
$et_idd_db = new CaT\Plugins\EduTracking\Purposes\IDD\ilDB($db, $app_event_handler);

while ($rec = $db->fetchAssoc($res)) {
    $edu_tracking = new ilObjEduTracking((int) $rec['ref_id']);
    $et_idd_db->selectFor($edu_tracking)->update();
}
?>

<#18>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\ilDB($ilDB, $app_event_handler);
$gti_db->createTable();
?>

<#19>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\ilDB($ilDB, $app_event_handler);
$gti_db->createPrimaryKey();
?>

<#20>
<?php
$q = 'SELECT obj_id FROM ' . CaT\Plugins\EduTracking\Purposes\IDD\ilDB::TABLE_NAME;
global $DIC;
$db = $DIC['ilDB'];
$res = $db->query($q);
$obj_s = [];
while ($rec = $db->fetchAssoc($res)) {
    $obj_s[] = (int) $rec['obj_id'];
}

if (count($obj_s) > 0) {
    $values = '(' . implode('),(', $obj_s) . ')';
    $db->manipulate(
        'INSERT INTO ' . CaT\Plugins\EduTracking\Purposes\GTI\ilDB::TABLE_NAME
        . '	(obj_id) VALUES ' . $values
    );
}
?>

<#21>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/EduTracking/classes/class.ilObjEduTracking.php");
global $DIC;
$db = $DIC["ilDB"];
$log = $DIC["ilLog"];

$query = "SELECT object_data.obj_id, xetr_wbd_data.obj_id AS wbd, xetr_idd_data.obj_id AS idd," . PHP_EOL
    . "xetr_gti_data.obj_id AS gti, object_reference.ref_id" . PHP_EOL
    . "FROM object_data" . PHP_EOL
    . "JOIN object_reference ON object_reference.obj_id = object_data.obj_id" . PHP_EOL
    . "LEFT JOIN xetr_wbd_data ON xetr_wbd_data.obj_id = object_data.obj_id" . PHP_EOL
    . "LEFT JOIN xetr_idd_data ON xetr_idd_data.obj_id = object_data.obj_id" . PHP_EOL
    . "LEFT JOIN xetr_gti_data ON xetr_gti_data.obj_id = object_data.obj_id" . PHP_EOL
    . "WHERE type = 'xetr'";

$res = $db->query($query);
$log->dump($res);
$done = [];
while ($row = $db->fetchAssoc($res)) {
    if (!in_array($row["obj_id"], $done)
        && is_null($row["wbd"])
        && is_null($row["idd"])
        && is_null($row["gti"])
    ) {
        $obj = new ilObjEduTracking($row["ref_id"]);

        if (is_null($row["wbd"])) {
            $actions = $obj->getActionsFor("wbd");
            $actions->createEmpty();
        }

        if (is_null($row["idd"])) {
            $actions = $obj->getActionsFor("idd");
            $actions->createEmpty();
        }


        if (is_null($row["gti"])) {
            $actions = $obj->getActionsFor("gti");
            $actions->createEmpty();
        }

        $parent = $obj->getParentCourse();
        if (is_null($parent)) {
            $log->write("No parent course");
        } else {
            $log->write("Parent course id: " . $parent->getId());
        }

        $obj->delete();
        $done[] = $row["obj_id"];
    }
}
?>

<#22>
<?php

$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($ilDB);
$gti_db->update1();
?>

<#23>
<?php

$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\Configuration\ilDB($ilDB);
$gti_db->update2();
$gti_db->update3();
$gti_db->update4();
?>

<#24>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\ilDB($ilDB, $app_event_handler);
$gti_db->update1();
?>

<#25>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$gti_db = new CaT\Plugins\EduTracking\Purposes\GTI\ilDB($ilDB, $app_event_handler);
$gti_db->update2();
?>

<#26>
<?php
$query = <<<SQL
SELECT
	object_reference.ref_id AS crs_ref_id,
	oref.obj_id AS child_obj_id
FROM
	tree
JOIN object_reference ON tree.child = object_reference.ref_id
JOIN object_data ON object_reference.obj_id = object_data.obj_id
JOIN tree t2 ON t2.path >= tree.path
	AND t2.path <= CONCAT(tree.path, ".Z")
JOIN object_reference oref ON oref.ref_id = t2.child
	AND oref.deleted IS NULL
JOIN object_data od ON od.obj_id = oref.obj_id
	AND od.type = "xetr"
JOIN xetr_gti_data ON xetr_gti_data.obj_id = od.obj_id
WHERE
	tree.path BETWEEN "1" AND "1.Z"
		AND tree.tree = 1
		AND object_data.type = "crs"
		AND object_reference.deleted IS NULL
		AND xetr_gti_data.set_trainingtime_manually = 0
		AND (xetr_gti_data.minutes IS NULL OR xetr_gti_data.minutes = 0)
ORDER BY crs_ref_id, child_obj_id DESC
SQL;

global $DIC;
$db = $DIC["ilDB"];

$pl = ilPluginAdmin::getPluginObjectById("xetr");

$res = $db->query($query);
while ($row = $db->fetchAssoc($res)) {
    $minutes = $pl->getCourseTrainingtimeInMinutes((int) $row["crs_ref_id"]);
    $db->manipulate("UPDATE xetr_gti_data SET minutes = $minutes WHERE obj_id = " . $row["child_obj_id"]);
}
?>

<#27>
<?php

global $DIC;
$app_event_handler = $DIC['ilAppEventHandler'];
$wbd_db = new CaT\Plugins\EduTracking\Purposes\WBD\ilDB($ilDB, $app_event_handler);
$wbd_db->update1();
?>

<#28>
<?php
global $DIC;
$db = $DIC["ilDB"];
$q = "UPDATE xetr_idd_data SET minutes = 0 WHERE minutes IS NULL";
$db->manipulate($q);
?>