<#1>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createSequence();
?>
<#4>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->update1();
?>
<#5>
<?php

$db = new \CaT\Plugins\WBDManagement\GutBeraten\ilDB($ilDB);
$db->createTable();
?>
<#6>
<?php

$db = new \CaT\Plugins\WBDManagement\GutBeraten\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#7>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->update2();
?>
<#8>
<?php

$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->update3();
?>
<#9>
<?php
global $DIC;
$db = $DIC["ilDB"];

$q = "SELECT id FROM object_data od JOIN " . \CaT\Ente\ILIAS\ilProviderDB::PROVIDER_TABLE . " prv ON prv.owner = od.obj_id WHERE od.type = 'xwbm'";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    $db->manipulate("DELETE FROM " . \CaT\Ente\ILIAS\ilProviderDB::PROVIDER_TABLE . " WHERE id = " . $db->quote($row["id"], "integer"));
    $db->manipulate("DELETE FROM " . \CaT\Ente\ILIAS\ilProviderDB::COMPONENT_TABLE . " WHERE id = " . $db->quote($row["id"], "integer"));
}
?>