<#1>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php
;
?>
<#4>
<?php
;
?>
<#5>
<?php

$db = new \CaT\Plugins\Accomodation\Reservation\ilDB($ilDB);
$db->createTable();
?>
<#6>
<?php

$db = new \CaT\Plugins\Accomodation\Reservation\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#7>
<?php
;
?>
<#8>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$xoac_type_id = ilDBUpdateNewObjectType::addNewType('xoac', 'Accomodations');
$order = 8400;

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    'book_accomodation',
    'Book Accomodations',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($xoac_type_id, $new_ops_id);

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    'edit_reservations',
    'Edit Reservations of Others',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($xoac_type_id, $new_ops_id);
?>
<#9>
<?php
;
?>
<#10>
<?php
;
?>

<#11>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->stepUpLocationId();
?>

<#12>
<?php

$plug = 'Accomodation';
$plug_id = 'xoac';
$class_name = CaT\Plugins\Accomodation\UnboundProvider::class;

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
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/class.ilObjAccomodation.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xoac'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#14>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->update1();
?>

<#15>
<?php
$query = "UPDATE xoac_objects SET mailsettings_from_venue = 1";
$ilDB->manipulate($query);
?>

<#16>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->update2();
?>

<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/class.ilObjAccomodation.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\Accomodation\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xoac'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/class.ilObjAccomodation.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xoac'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#19>
<?php
;
?>

<#20>
<?php

$plug = 'Accomodation';
$plug_id = 'xoac';
$class_name = CaT\Plugins\Accomodation\SharedUnboundProvider::class;

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
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/SharedUnboundProvider.php";

    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#21>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/class.ilObjAccomodation.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xoac'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#22>
<?php
$query = "DELETE FROM xoac_reservations WHERE xoac_reservations.oac_obj_id NOT IN (SELECT obj_id FROM object_reference)";
$ilDB->manipulate($query);
?>

<#23>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->update3();
?>

<#24>
<?php
;
?>

<#25>
<?php
;
?>

<#26>
<?php
;
?>

<#27>
<?php
$table_name = "xoac_migration_log";
if ($ilDB->tableExists($table_name)) {
    $query = "DROP TABLE $table_name";
    $ilDB->query($query);
}
?>

<#28>
<?php

$db = new \CaT\Plugins\Accomodation\Reservation\Note\ilDB($ilDB);
$db->createTable();
?>

<#29>
<?php

$db = new \CaT\Plugins\Accomodation\Reservation\Note\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#30>
<?php

$db = new \CaT\Plugins\Accomodation\ObjSettings\ilDB($ilDB);
$db->update4();
?>

<#31>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accomodation/classes/class.ilObjAccomodation.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xoac'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccomodation();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>
