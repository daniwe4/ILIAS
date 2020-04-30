<#1>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Children\ilDB($ilDB);
$settings_db->createTable();
?>

<#2>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Children\ilDB($ilDB);
$settings_db->createPrimaryKey();
?>

<#3>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Children\ilDB($ilDB);
$settings_db->update1();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
$query = "SELECT od.obj_id, oref.ref_id FROM object_data od JOIN object_reference oref ON od.obj_id = oref.obj_id WHERE oref.deleted IS NULL AND type = 'xcps'";
$res = $ilDB->query($query);
$done = array();
while ($row = $ilDB->fetchAssoc($res)) {
    $copy_object = new ilObjCopySettings((int) $row["ref_id"]);
    $txt = $copy_object->txtClosure();
    $parent = $copy_object->getParentContainer();
    if ($parent !== null && !in_array($parent->getId(), $done)) {
        $title = $parent->getTitle();
        $title = str_replace($txt("template_prefix") . ": ", "", $title);
        $parent->setTitle($txt("template_prefix") . ": " . $title);
        $parent->update();
        $done[] = $parent->getId();
    }
}
?>

<#5>
<?php

$settings_db = new \CaT\Plugins\CopySettings\TemplateCourses\ilDB($ilDB);
$settings_db->createTable();
?>

<#6>
<?php

$settings_db = new \CaT\Plugins\CopySettings\TemplateCourses\ilDB($ilDB);
$settings_db->createPrimaryKey();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
$query = "SELECT od.obj_id, oref.ref_id FROM object_data od JOIN object_reference oref ON od.obj_id = oref.obj_id WHERE oref.deleted IS NULL AND type = 'xcps'";
$res = $ilDB->query($query);
$done = array();
while ($row = $ilDB->fetchAssoc($res)) {
    $copy_object = new ilObjCopySettings((int) $row["ref_id"]);
    $txt = $copy_object->txtClosure();
    $parent = $copy_object->getParentContainer();
    if ($parent !== null && !in_array($parent->getId(), $done)) {
        $copy_object->getTemplateCoursesDB()->create((int) $row["obj_id"], (int) $parent->getId(), (int) $parent->getRefId());
    }
}
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\CopySettings\UnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/UnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcps'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCopySettings();
    $obj->setId($row["obj_id"]);
    $updated = false;
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
        $updated = true;
    }
    if (!$updated) {
        $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
        $provider_db->update($provider);
    }
}
?>

<#9>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->createTable();
?>

<#10>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->createPrimaryKey();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
$query = "SELECT od.obj_id AS obj_id, oref.ref_id, xps.obj_id AS xps_id"
        . " FROM object_data od"
        . " JOIN object_reference oref ON od.obj_id = oref.obj_id"
        . " LEFT JOIN xcps_settings xps ON od.obj_id = xps.obj_id"
        . " WHERE oref.deleted IS NULL AND type = 'xcps'"
        . " HAVING xps.obj_id IS NULL";
$res = $ilDB->query($query);
$done = array();
while ($row = $ilDB->fetchAssoc($res)) {
    $ref_id = (int) $row["ref_id"];
    if (!in_array($ref_id, $done)) {
        $copy_object = new ilObjCopySettings($ref_id);
        $copy_object->setId((int) $row["obj_id"]);
        $copy_object->getSettingsActions()->create();
        $done[] = $ref_id;
    }
}
?>

<#12>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update1();
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CopySettings/classes/class.ilObjCopySettings.php");
$q = 'SELECT ref_id'
    . '	FROM object_data'
    . '	JOIN object_reference USING(obj_id)'
    . '	WHERE deleted IS NULL AND type = ' . $ilDB->quote('xcps', 'text');
$res = $ilDB->query($q);
while ($rec = $ilDB->fetchAssoc($res)) {
    $copy_object = new ilObjCopySettings((int) $rec['ref_id']);
    $copy_object->doRead();
    $copy_object->doUpdate();
}
?>

<#14>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update2();
?>

<#15>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update3();
?>

<#16>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update4();
?>

<#17>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update5();
?>

<#18>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update6();
?>

<#19>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update7();
?>

<#20>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update8();
?>

<#21>
<?php
global $DIC;
$db = $DIC["ilDB"];
$query = "SELECT obj_id, role_ids FROM xcps_settings";
$res = $db->query($query);
while ($row = $db->fetchAssoc($res)) {
    $ret = serialize([(int) $row["role_ids"]]);
    $update = "UPDATE xcps_settings SET role_ids = " . $db->quote($ret, "text") . " WHERE obj_id = " . $row["obj_id"];
    $db->manipulate($update);
}
?>

<#22>
<?php
global $DIC;
$db = $DIC["ilDB"];
$query = "SELECT obj_id, role_ids FROM xcps_settings";
$res = $db->query($query);
while ($row = $db->fetchAssoc($res)) {
    $ret = unserialize($row["role_ids"]);
    $ret = array_filter($ret, function ($role_id) {
        return $role_id != 2;
    });
    $ret = serialize($ret);
    $update = "UPDATE xcps_settings SET role_ids = " . $db->quote($ret, "text") . " WHERE obj_id = " . $row["obj_id"];
    $db->manipulate($update);
}
?>

<#23>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update9();
?>

<#24>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update10();
?>

<#25>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update11();
?>

<#26>
<?php
global $DIC;
$db = $DIC["ilDB"];
$q = "UPDATE xcps_settings SET edit_venue = 1";
$db->manipulate($q);
?>

<#27>
<?php
global $DIC;
$db = $DIC["ilDB"];
$q = "UPDATE xcps_settings SET edit_provider = 1";
$db->manipulate($q);
?>

<#28>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update12();
?>

<#29>
<?php

$settings_db = new \CaT\Plugins\CopySettings\Settings\ilDB($ilDB);
$settings_db->update13();
?>
