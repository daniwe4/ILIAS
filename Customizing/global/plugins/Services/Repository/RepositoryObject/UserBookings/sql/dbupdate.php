<#1>
<?php

$category_db = new CaT\Plugins\UserBookings\Settings\ilDB($ilDB);

$category_db->createTable();
?>

<#2>
<?php

$category_db = new CaT\Plugins\UserBookings\Settings\ilDB($ilDB);

$category_db->createPrimaryKey();
?>

<#3>
<?php

$category_db = new CaT\Plugins\UserBookings\Settings\ilDB($ilDB);

$category_db->migrateExistingObjects();
?>

<#4>
<?php

$plug = 'UserBookings';
$plug_id = 'xubk';
$class_name = CaT\Plugins\UserBookings\UnboundProvider::class;

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
    $obj = new ilObjUserBookings();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "root", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/UserBookings/classes/class.ilObjUserBookings.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xubk'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjUserBookings();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#6>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$db->tableColumnExists("xubk_settings", "local_evaluation")) {
    $db->addTableColumn(
        "xubk_settings",
        "local_evaluation",
        [
            "type" => "integer",
            "notnull" => true,
            "length" => 1
        ]
    );
}
?>

<#7>
<?php
global $DIC;
$db = $DIC["ilDB"];
if (!$db->tableColumnExists("xubk_settings", "recommendation_allowed")) {
    $field = array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => false,
        'default' => 1
    );
    $db->addTableColumn("xubk_settings", "recommendation_allowed", $field);
}
?>
