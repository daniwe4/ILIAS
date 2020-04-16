<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->createTable();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->createPrimaryKey();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$participant_db = new \CaT\Plugins\Webinar\Participant\ilDB($ilDB);
$participant_db->createTable();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$participant_db = new \CaT\Plugins\Webinar\Participant\ilDB($ilDB);
$participant_db->createPrimaryKey();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\CSN\ilDB($ilDB);
$csn_db->createTable();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\CSN\ilDB($ilDB);
$csn_db->createPrimaryKeySettings();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\CSN\ilDB($ilDB);
$csn_db->createPrimaryKeyParticipants();
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\CSN\ilDB($ilDB);
$csn_db->createSequenceParticipants();
?>

<#9>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->tableUpdate1();
?>

<#10>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$participant_db = new \CaT\Plugins\Webinar\Participant\ilDB($ilDB);
$participant_db->tableUpdate1();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\Generic\ilDB($ilDB);
$csn_db->createTable();
?>

<#12>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\Generic\ilDB($ilDB);
$csn_db->createPrimaryKeySettings();
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\Generic\ilDB($ilDB);
$csn_db->createPrimaryKeyParticipants();
?>

<#14>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\Generic\ilDB($ilDB);
$csn_db->createSequenceParticipants();
?>

<#15>
<?php
global $DIC;
$db = $DIC->database();
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($db, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/class.ilObjWebinar.php");
$query = $db->query("SELECT obj_id FROM object_data WHERE type = 'xwbr'");
while ($res = $db->fetchAssoc($query)) {
    $obj = new ilObjWebinar();
    $obj->setId($res["obj_id"]);
    $provider_db->createSeparatedUnboundProvider($obj, "crs", CaT\Plugins\Webinar\UnboundProvider::class, "Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/UnboundProvider.php");
}
?>

<#16>
<?php
;
?>

<#17>
<?php

$plug = 'Webinar';
$plug_id = 'xwbr';
$class_name = CaT\Plugins\Webinar\UnboundProvider::class;

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
    $obj = new ilObjWebinar();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/class.ilObjWebinar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjWebinar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/class.ilObjWebinar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjWebinar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#20>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->tableUpdate2();
?>

<#21>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->tableUpdate3();
?>

<#22>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$csn_db = new \CaT\Plugins\Webinar\VC\CSN\ilDB($ilDB);
$csn_db->update1();
?>

<#23>
<?php
$query = "SELECT obj_id, upload_required FROM xwbr_data WHERE vc_type = 'CSN'";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $where = array("obj_id" => array("integer", $row["obj_id"]));
    $values = array("upload_required" => array("integer", (bool) $row["upload_required"]));
    $ilDB->update("xwbr_csn_settings", $values, $where);
}
?>

<#24>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/vendor/autoload.php");
$settings_db = new \CaT\Plugins\Webinar\Settings\ilDB($ilDB);
$settings_db->tableUpdate4();
?>

<#25>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/class.ilObjWebinar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\Webinar\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjWebinar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#26>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Webinar/classes/class.ilObjWebinar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjWebinar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#27>
<?php
global $DIC;
$db = $DIC["ilDB"];
if ($db->tableColumnExists('xwbr_generic_settings', 'password')) {
    $field = [
        'type' => 'text',
        'length' => 150,
        'notnull' => false
    ];

    $db->modifyTableColumn('xwbr_generic_settings', 'password', $field);
}
?>