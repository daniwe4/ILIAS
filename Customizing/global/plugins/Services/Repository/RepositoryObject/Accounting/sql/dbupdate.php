<#1>
<?php
global $ilUser;


$db_settings = new \CaT\Plugins\Accounting\Settings\ilDB($ilDB, $ilUser);
$db_costtype = new \CaT\Plugins\Accounting\Config\CostType\ilDB($ilDB, $ilUser);
$db_vat_rate = new \CaT\Plugins\Accounting\Config\VatRate\ilDB($ilDB, $ilUser);
$db_data = new \CaT\Plugins\Accounting\Data\ilDB($ilDB, $ilUser);
$db_settings->install();
$db_costtype->install();
$db_vat_rate->install();
$db_data->install();
?>

<#2>
<?php
;
?>

<#3>
<?php
    $ilDB->modifyTableColumn('xacc_data', 'tax', array("type" => "float"));
    $ilDB->renameTableColumn('xacc_config_vat_rate', "val_int", "val_float");
    $ilDB->modifyTableColumn('xacc_config_vat_rate', 'val_float', array("type" => "float"));
?>

<#4>
<?php
    $ilDB->addTableColumn('xacc_config_costtype', "active", array("type" => "integer", "length" => "1"));
?>

<#5>
<?php
    $ilDB->addTableColumn('xacc_config_vat_rate', "active", array("type" => "integer", "length" => "1"));
?>

<#6>
<?php
;
?>

<#7>
<?php
global $ilUser;

$db_settings = new \CaT\Plugins\Accounting\Settings\ilDB($ilDB, $ilUser);
$db_settings->setPrimaryKey();
?>

<#8>
<?php
global $ilUser;

$data_db = new CaT\Plugins\Accounting\Data\ilDB($ilDB, $ilUser);

$data_db->update1();
?>

<#9>
<?php
global $ilUser;

$data_db = new CaT\Plugins\Accounting\Data\ilDB($ilDB, $ilUser);

$data_db->update2();
?>

<#10>
<?php
global $ilUser;

$data_db = new CaT\Plugins\Accounting\Data\ilDB($ilDB, $ilUser);

$data_db->update3();
?>

<#11>
<?php
global $ilUser;
$query = "SELECT xacc_objects.obj_id AS xacc_id, object_data.obj_id AS object_id FROM object_data LEFT JOIN xacc_objects ON xacc_objects.obj_id = object_data.obj_id WHERE object_data.type = 'xacc' HAVING xacc_id IS NULL";
$res = $ilDB->query($query);

$data_db = new CaT\Plugins\Accounting\Settings\ilDB($ilDB, $ilUser);

while ($row = $ilDB->fetchAssoc($res)) {
    $data_db->insert((int) $row["object_id"], false);
}
?>

<#12>
<?php
global $ilUser;
$query = "SELECT xacc_objects.obj_id AS xacc_id, object_data.obj_id AS object_id FROM object_data LEFT JOIN xacc_objects ON xacc_objects.obj_id = object_data.obj_id WHERE object_data.type = 'xacc' HAVING xacc_id IS NULL";
$res = $ilDB->query($query);

$data_db = new CaT\Plugins\Accounting\Settings\ilDB($ilDB, $ilUser);

while ($row = $ilDB->fetchAssoc($res)) {
    $data_db->insert((int) $row["object_id"], false);
}
?>

<#13>
<?php

$db_settings = new \CaT\Plugins\Accounting\Fees\Fee\ilDB($ilDB);
$db_settings->createTable();
?>

<#14>
<?php

$db_settings = new \CaT\Plugins\Accounting\Fees\Fee\ilDB($ilDB);
$db_settings->setPrimaryKey();
?>

<#15>
<?php
$plug = 'Accounting';
$plug_id = 'xacc';
$class_name = CaT\Plugins\Accounting\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);

$query = "SELECT od.obj_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = '$plug_id' AND oref.deleted IS NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccounting();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#16>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/classes/class.ilObjAccounting.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT od.obj_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = 'xacc' AND oref.deleted IS NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccounting();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Accounting/classes/class.ilObjAccounting.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT od.obj_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = 'xacc' AND oref.deleted IS NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAccounting();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#18>
<?php
global $DIC;


$db_settings = new \CaT\Plugins\Accounting\Settings\ilDB($ilDB, $DIC["ilUser"]);
$db_settings->update1();
?>

<#19>
<?php

$db_settings = new \CaT\Plugins\Accounting\Fees\CancellationFee\ilDB($ilDB);
$db_settings->createTable();
?>

<#20>
<?php

$db_settings = new \CaT\Plugins\Accounting\Fees\CancellationFee\ilDB($ilDB);
$db_settings->setPrimaryKey();
?>

<#21>
<?php

$db_settings = new \CaT\Plugins\Accounting\Config\Cancellation\Scale\ilDB($ilDB);
$db_settings->createTable();
?>

<#22>
<?php

$db_settings = new \CaT\Plugins\Accounting\Config\Cancellation\Scale\ilDB($ilDB);
$db_settings->createPrimaryKey();
?>

<#23>
<?php

$db_settings = new \CaT\Plugins\Accounting\Config\Cancellation\Scale\ilDB($ilDB);
$db_settings->createSequence();
?>
