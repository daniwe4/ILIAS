<#1>
<?php

use \CaT\Plugins\RoomSetup\ServiceOptions\ilDB as ServiceOptionsDB;

$db = new ServiceOptionsDB($ilDB);
$db->install();
?>

<#2>
<?php

use \CaT\Plugins\RoomSetup\Equipment\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->install();
?>

<#3>
<?php

use \CaT\Plugins\RoomSetup\ServiceOptions\ilDB as ServiceOptionsDB;

$db = new ServiceOptionsDB($ilDB);
$db->updateTable1();
?>

<#4>
<?php

use \CaT\Plugins\RoomSetup\Equipment\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->update1();
?>

<#5>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->createTable();
?>

<#6>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->createPrimary();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSetup/classes/class.ilObjRoomSetup.php");
$query = "SELECT oref.ref_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = 'xrse' AND oref.deleted IS NULL";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $obj = new ilObjRoomSetup((int) $row["ref_id"]);
    $settings_db = $obj->getSettingsDB($ilDB);
    $settings_db->create((int) $obj->getId(), "venue_config");
}
?>

<#8>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->update1();
?>

<#9>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->update2();
?>

<#10>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->update3();
?>

<#11>
<?php

use \CaT\Plugins\RoomSetup\Settings\ilDB as EquipmentDB;

$db = new EquipmentDB($ilDB);
$db->update4();
?>

<#12>
<?php
$plug = 'RoomSetup';
$plug_id = 'xrse';
$class_name = CaT\Plugins\RoomSetup\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjRoomSetup();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#13>
<?php
$plug = 'RoomSetup';
$plug_id = 'xrse';
$class_name = CaT\Plugins\RoomSetup\SharedUnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjRoomSetup();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/SharedUnboundProvider.php";

    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

