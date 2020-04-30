<#1>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->createTable();
?>

<#2>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->createPrimaryKey();
?>

<#3>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->createSequence();
?>

<#4>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update1();
?>

<#5>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update2();
?>

<#6>
<?php

$plug = 'Agenda';
$plug_id = 'xage';
$class_name = CaT\Plugins\Agenda\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAgenda();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#7>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update3();
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/classes/class.ilObjAgenda.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xage'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAgenda();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#9>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/classes/class.ilObjAgenda.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\Agenda\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/Agenda/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xage'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAgenda();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }

    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
}
?>

<#10>
<?php

global $DIC;
$db = new CaT\Plugins\Agenda\Config\Blocks\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createTable();
?>

<#11>
<?php

global $DIC;
$db = new CaT\Plugins\Agenda\Config\Blocks\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createPrimaryKey();
?>

<#12>
<?php

global $DIC;
$db = new CaT\Plugins\Agenda\Config\Blocks\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createSequence();
?>

<#13>
<?php

$wbd_db = new CaT\Plugins\Agenda\Settings\ilDB($ilDB);
$wbd_db->createTable();
?>

<#14>
<?php

$wbd_db = new CaT\Plugins\Agenda\Settings\ilDB($ilDB);
$wbd_db->createPrimaryKey();
?>
<#15>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update4();
?>
<#16>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update5();
?>
<#17>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update6();
?>
<#18>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update7();
?>
<#19>
<?php

$wbd_db = new CaT\Plugins\Agenda\AgendaEntry\ilDB($ilDB);
$wbd_db->update8();
?>