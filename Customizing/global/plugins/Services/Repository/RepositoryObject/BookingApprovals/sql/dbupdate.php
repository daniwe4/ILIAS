<#1>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createRequestsTable();
?>

<#2>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createSequenceRequests();
?>

<#3>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createPrimaryKeysRequests();
?>

<#4>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createApprovalsTable();
?>

<#5>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createSequenceApprovals();
?>

<#6>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->createPrimaryKeysApprovals();
?>

<#7>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->updateAddActingUser();
?>

<#8>
<?php

$db = new CaT\Plugins\BookingApprovals\Settings\ilDB($ilDB);
$db->createTable();
?>

<#9>
<?php

$db = new CaT\Plugins\BookingApprovals\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#10>
<?php
;
?>

<#11>
<?php

$plug = 'BookingApprovals';
$plug_id = 'xbka';
$class_name = CaT\Plugins\BookingApprovals\UnboundProvider::class;

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
    $obj = new ilObjBookingApprovals();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "root", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#12>
<?php

$db = new CaT\Plugins\BookingApprovals\Approvals\ilDB($ilDB);
$db->addCrsId();
?>

<#13>
<?php
$q = "SELECT A.id, A.crs_ref_id, B.obj_id FROM xbka_requests A JOIN object_reference B WHERE A.crs_ref_id = B.ref_id";
$res = $ilDB->query($q);

while ($row = $ilDB->fetchAssoc($res)) {
    if (is_null($row["obj_id"])) {
        continue;
    }
    $u = "UPDATE xbka_requests SET crs_id = " . $row["obj_id"] . " WHERE id = " . $row["id"];
    $ilDB->manipulate($u);
}
?>