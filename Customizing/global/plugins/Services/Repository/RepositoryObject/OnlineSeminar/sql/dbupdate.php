<#1>
<?php

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->createTable();
?>

<#2>
<?php

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->createPrimaryKey();
?>

<#3>
<?php

$participant_db = new \CaT\Plugins\OnlineSeminar\Participant\ilDB($ilDB);
$participant_db->createTable();
?>

<#4>
<?php

$participant_db = new \CaT\Plugins\OnlineSeminar\Participant\ilDB($ilDB);
$participant_db->createPrimaryKey();
?>

<#5>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\CSN\ilDB($ilDB);
$csn_db->createTable();
?>

<#6>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\CSN\ilDB($ilDB);
$csn_db->createPrimaryKeySettings();
?>

<#7>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\CSN\ilDB($ilDB);
$csn_db->createPrimaryKeyParticipants();
?>

<#8>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\CSN\ilDB($ilDB);
$csn_db->createSequenceParticipants();
?>

<#9>
<?php

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->tableUpdate1();
?>

<#10>
<?php

$participant_db = new \CaT\Plugins\OnlineSeminar\Participant\ilDB($ilDB);
$participant_db->tableUpdate1();
?>

<#11>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\Generic\ilDB($ilDB);
$csn_db->createTable();
?>

<#12>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\Generic\ilDB($ilDB);
$csn_db->createPrimaryKeySettings();
?>

<#13>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\Generic\ilDB($ilDB);
$csn_db->createPrimaryKeyParticipants();
?>

<#14>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\Generic\ilDB($ilDB);
$csn_db->createSequenceParticipants();
?>

<#15>
<?php
global $DIC;
$db = $DIC->database();
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($db, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php");
$query = $db->query("SELECT obj_id FROM object_data WHERE type = 'xwbr'");
while ($res = $db->fetchAssoc($query)) {
    $obj = new ilObjOnlineSeminar();
    $obj->setId($res["obj_id"]);
    $provider_db->createSeparatedUnboundProvider($obj, "crs", CaT\Plugins\OnlineSeminar\UnboundProvider::class, "Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/UnboundProvider.php");
}
?>

<#16>
<?php
;
?>

<#17>
<?php

$plug = 'OnlineSeminar';
$plug_id = 'xwbr';
$class_name = CaT\Plugins\OnlineSeminar\UnboundProvider::class;

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
    $obj = new ilObjOnlineSeminar();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#20>
<?php

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->tableUpdate2();
?>

<#21>
<?php

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->tableUpdate3();
?>

<#22>
<?php

$csn_db = new \CaT\Plugins\OnlineSeminar\VC\CSN\ilDB($ilDB);
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

$settings_db = new \CaT\Plugins\OnlineSeminar\Settings\ilDB($ilDB);
$settings_db->tableUpdate4();
?>

<#25>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\OnlineSeminar\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
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
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xwbr'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
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

<#28>
<?php
global $DIC;
$db = $DIC["ilDB"];

$q = "UPDATE mail_man_tpl SET m_message = REPLACE(m_message, 'WEBINAR', 'ONLINE_SEMINAR')";

$db->manipulate($q);
?>

<#29>
<?php
global $DIC;

$plug_id = 'xwbr';
$new_class_name = CaT\Plugins\OnlineSeminar\UnboundProvider::class;
$old_class_name = str_replace("OnlineSeminar", "Webinar", $new_class_name);
$old_class_name_sql = str_replace('\\', '\\\\', $old_class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$old_class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$old_class_name_sql'";
$ilDB->query($query);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);

$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php";
require_once($req);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        ."/Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $new_class_name, $path);
    $provider_db->update($provider);
}
?>

<#30>
<?php
global $DIC;

$plug_id = 'xwbr';
$new_class_name = CaT\Plugins\OnlineSeminar\SharedUnboundProvider::class;
$old_class_name = str_replace("OnlineSeminar", "Webinar", $new_class_name);
$old_class_name_sql = str_replace('\\', '\\\\', $old_class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$old_class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$old_class_name_sql'";
$ilDB->query($query);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);

$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/class.ilObjOnlineSeminar.php";
require_once($req);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjOnlineSeminar();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        ."/Customizing/global/plugins/Services/Repository/RepositoryObject/OnlineSeminar/classes/UnboundProvider.php";

    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $new_class_name, $path);
    $provider_db->update($provider);
}
?>

<#31>
<?php
global $DIC;

$plug_id = 'xwbr';
$new_class_name = CaT\Plugins\OnlineSeminar\UnboundGlobalProvider::class;
$old_class_name = str_replace("OnlineSeminar", "Webinar", $new_class_name);
$old_class_name_sql = str_replace('\\', '\\\\', $old_class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$old_class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$old_class_name_sql'";
$ilDB->query($query);

CaT\Plugins\OnlineSeminar\UnboundGlobalProvider::createGlobalProvider();
?>

<#32>
<?php

$old = CLIENT_DATA_DIR."/Plugin/Webinar";
$new = CLIENT_DATA_DIR."/Plugin/OnlineSeminar";

if (@is_dir($old)) {
    rename($old, $new);
}
?>
