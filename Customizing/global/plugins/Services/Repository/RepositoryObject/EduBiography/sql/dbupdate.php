<#1>
<?php

use  CaT\Plugins\EduBiography\Settings as Settings;

global $DIC;
$db = $DIC['ilDB'];
$fields = [
    Settings\SettingsRepository::PROP_ID => [
            'type' => 'integer'
            ,'length' => 4
            ,'notnull' => true]
    ,Settings\SettingsRepository::PROP_IS_ONLINE => [
            'type' => 'integer'
            ,'length' => 1
            ,'notnull' => true]
];
if (!$db->tableExists(Settings\SettingsRepository::DB_TABLE)) {
    $db->createTable(Settings\SettingsRepository::DB_TABLE, $fields);
}

?>
<#2>
<?php

use  CaT\Plugins\EduBiography\Settings as Settings;

global $DIC;
$db = $DIC['ilDB'];
$db->addPrimaryKey(Settings\SettingsRepository::DB_TABLE, [Settings\SettingsRepository::PROP_ID]);
?>
<#3>
<?php

use  CaT\Plugins\EduBiography\Settings as Settings;

global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists(Settings\SettingsRepository::DB_TABLE, Settings\SettingsRepository::PROP_HAS_SUPERIOR_OVERVIEW)) {
    $db->addTableColumn(Settings\SettingsRepository::DB_TABLE, Settings\SettingsRepository::PROP_HAS_SUPERIOR_OVERVIEW, [ 'type' => 'integer', 'length' => 1, 'notnull' => true, 'default' => 0]);
}
?>

<#4>
<?php
;
?>

<#5>
<?php
;
?>

<#6>
<?php
;
?>

<#7>
<?php
$plug = 'EduBiography';
$plug_id = 'xebr';
$class_name = CaT\Plugins\EduBiography\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = 'EduBiography\\\\UnboundProvider')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$class_name_sql'";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = 'EduBiography\\\\UnboundProvider'";
$ilDB->query($query);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjEduBiography();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "root", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#8>
<?php
;
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

global $DIC;
$db = new CaT\Plugins\EduBiography\Settings\SettingsRepository($DIC["ilDB"]);
$db->update1();
?>

<#12>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Settings\SettingsRepository($DIC["ilDB"]);
$db->update2();
?>

<#13>
<?php
;
?>

<#14>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Settings\SettingsRepository($DIC["ilDB"]);
$db->update3();
?>

<#15>
<?php
;
?>

<#16>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\ilDB($DIC['ilDB'], $DIC['ilUser']);
$db->createTable();
?>

<#17>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\ilDB($DIC['ilDB'], $DIC['ilUser']);
$db->createSequence();
?>

<#18>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\ilDB($DIC['ilDB'], $DIC['ilUser']);
$db->createPrimaryKey();
?>

<#19>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\ilDB($DIC['ilDB'], $DIC['ilUser']);
$db->update1();
?>

<#20>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\OverviewCertificate\ilDB($DIC['ilDB']);
$db->createTable();
?>

<#21>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\OverviewCertificate\ilDB($DIC['ilDB']);
$db->createPrimaryKey();
?>

<#22>
<?php

global $DIC;
$fix = new CaT\Plugins\EduBiography\Fixes\ilFixParticipationStatusAfterDeleteCourse($DIC["ilDB"]);
$fix->run();
?>

<#23>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation\ilDB($DIC['ilDB']);
$db->createTable();
?>

<#24>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation\ilDB($DIC['ilDB']);
$db->createSequence();
?>

<#25>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Activation\ilDB($DIC['ilDB']);
$db->createPrimaryKey();
?>

<#26>
<?php

global $DIC;
$db = new CaT\Plugins\EduBiography\Config\OverviewCertificate\Schedules\ilDB($DIC['ilDB'], $DIC['ilUser']);
$db->update2();
?>