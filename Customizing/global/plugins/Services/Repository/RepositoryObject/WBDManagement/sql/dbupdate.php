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
