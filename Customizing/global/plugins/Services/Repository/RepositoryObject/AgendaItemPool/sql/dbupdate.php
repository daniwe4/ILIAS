<#1>
<?php

$db = new \CaT\Plugins\AgendaItemPool\Settings\ilDB($ilDB);
$db->createSettingsTable();
?>
<#2>
<?php

$db = new \CaT\Plugins\AgendaItemPool\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php

$db = new \CaT\Plugins\AgendaItemPool\Settings\ilDB($ilDB);
$db->createSequence();
?>
<#4>
<?php

$db = new \CaT\Plugins\AgendaItemPool\AgendaItem\ilDB($ilDB);
$db->createAgendaItemTable();
?>
<#5>
<?php

$db = new \CaT\Plugins\AgendaItemPool\AgendaItem\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#6>
<?php

$db = new \CaT\Plugins\AgendaItemPool\AgendaItem\ilDB($ilDB);
$db->createSequence();
?>
<#7>
<?php

$db = new CaT\Plugins\AgendaItemPool\Options\Topic\ilDB($ilDB);
$db->install();
?>
<#8>
<?php

$db = new CaT\Plugins\AgendaItemPool\Options\Topic\ilDB($ilDB);
$db->configurePrimaryKeys();
?>
<#9>
<?php

$db = new CaT\Plugins\AgendaItemPool\AgendaItem\ilDB($ilDB);
$db->update1();
?>
<#10>
<?php

$plug = 'AgendaItemPool';
$plug_id = 'xaip';
$class_name = CaT\Plugins\AgendaItemPool\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjAgendaItemPool();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "root", $class_name, $path);
    $provider_db->update($provider);
}
?>
<#11>
<?php

$plug = 'AgendaItemPool';
$plug_id = 'xaip';

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj_id = $row["obj_id"];
    $query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE owner = " . $ilDB->quote($obj_id, "integer") . ")";
    $ilDB->query($query);

    $query = "DELETE FROM ente_prvs WHERE owner = " . $ilDB->quote($obj_id, "integer");
    $ilDB->query($query);
}
?>