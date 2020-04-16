<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/HeaderConfiguration/ilDB.php");
$settings_db = new \CaT\Plugins\MaterialList\HeaderConfiguration\ilDB($ilDB);
$settings_db->install();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/Materials/ilDB.php");
$settings_db = new \CaT\Plugins\MaterialList\Materials\ilDB($ilDB);
$settings_db->install();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/Lists/ilDB.php");
$settings_db = new \CaT\Plugins\MaterialList\Lists\ilDB($ilDB);
$settings_db->install();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\MaterialList\Settings\ilDB($ilDB);
$settings_db->install();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/Settings/ilDB.php");
$settings_db = new \CaT\Plugins\MaterialList\Settings\ilDB($ilDB);
$settings_db->update1();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/class.ilObjMaterialList.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\MaterialList\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/MaterialList/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xmat'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjMaterialList();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#7>
<?php
global $DIC;
$ilDB = $DIC->database();

$query = "UPDATE xmat_settings SET recipient_mode = " . $ilDB->quote("course_venue", "text") . " WHERE recipient_mode IS NULL";
$ilDB->manipulate($query);
?>