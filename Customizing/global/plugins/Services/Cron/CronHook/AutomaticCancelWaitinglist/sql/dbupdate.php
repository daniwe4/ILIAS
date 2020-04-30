<#1>
<?php

global $DIC;
$db = new CaT\Plugins\AutomaticCancelWaitinglist\Log\ilDB($DIC["ilDB"]);
$db->createLogTable();
?>
<#2>
<?php

global $DIC;
$db = new CaT\Plugins\AutomaticCancelWaitinglist\Log\ilDB($DIC["ilDB"]);
$db->createSequence();
?>
<#3>
<?php

global $DIC;
$db = new CaT\Plugins\AutomaticCancelWaitinglist\Log\ilDB($DIC["ilDB"]);
$db->createPrimaryKey();
?>