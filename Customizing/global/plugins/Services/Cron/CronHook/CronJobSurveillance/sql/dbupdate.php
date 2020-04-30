<#1>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistTable();
?>
<#4>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistPrimaryKey();
?>
<#5>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistSequence();
?>
<#6>
<?php

$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->update1();
?>