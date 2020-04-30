<#1>
<?php

use \CaT\Plugins\Venues\Tags\Venue\ilDB as TagsDB;

$db = new TagsDB($ilDB);
$db->install();
?>

<#2>
<?php

use \CaT\Plugins\Venues\VenueAssignment\ilDB as AssignmentDB;

$db = new AssignmentDB($ilDB);
$db->install();
?>

<#3>
<?php

$db = new \CaT\Plugins\Venues\Venues\ilDB($ilDB);
$db->createSequence();
?>

<#4>
<?php

$db = new \CaT\Plugins\Venues\Venues\General\ilDB($ilDB);
$db->createTable();
?>

<#5>
<?php

$db = new \CaT\Plugins\Venues\Venues\Rating\ilDB($ilDB);
$db->createTable();
?>

<#6>
<?php

$db = new \CaT\Plugins\Venues\Venues\Address\ilDB($ilDB);
$db->createTable();
?>

<#7>
<?php

$db = new \CaT\Plugins\Venues\Venues\Contact\ilDB($ilDB);
$db->createTable();
?>

<#8>
<?php

$db = new \CaT\Plugins\Venues\Venues\Conditions\ilDB($ilDB);
$db->createTable();
?>

<#9>
<?php

$db = new \CaT\Plugins\Venues\Venues\General\ilDB($ilDB);
$db->createPrimary();
?>

<#10>
<?php

$db = new \CaT\Plugins\Venues\Venues\Rating\ilDB($ilDB);
$db->createPrimary();
?>

<#11>
<?php

$db = new \CaT\Plugins\Venues\Venues\Address\ilDB($ilDB);
$db->createPrimary();
?>

<#12>
<?php

$db = new \CaT\Plugins\Venues\Venues\Contact\ilDB($ilDB);
$db->createPrimary();
?>

<#13>
<?php

$db = new \CaT\Plugins\Venues\Venues\Conditions\ilDB($ilDB);
$db->createPrimary();
?>

<#14>
<?php

$db = new \CaT\Plugins\Venues\Venues\Capacity\ilDB($ilDB);
$db->createTable();
?>

<#15>
<?php

$db = new \CaT\Plugins\Venues\Venues\Capacity\ilDB($ilDB);
$db->createPrimary();
?>

<#16>
<?php

$db = new \CaT\Plugins\Venues\Venues\Service\ilDB($ilDB);
$db->createTable();
?>

<#17>
<?php

$db = new \CaT\Plugins\Venues\Venues\Service\ilDB($ilDB);
$db->createPrimary();
?>

<#18>
<?php

$db = new \CaT\Plugins\Venues\Venues\Costs\ilDB($ilDB);
$db->createTable();
?>

<#19>
<?php

$db = new \CaT\Plugins\Venues\Venues\Costs\ilDB($ilDB);
$db->createPrimary();
?>

<#20>
<?php

$db = new \CaT\Plugins\Venues\Venues\Capacity\ilDB($ilDB);
$db->update1();
?>

<#21>
<?php

$db = new \CaT\Plugins\Venues\Venues\Costs\ilDB($ilDB);
$db->update1();
?>

<#22>
<?php

$db = new \CaT\Plugins\Venues\Venues\Costs\ilDB($ilDB);
$db->migrate1();
?>

<#23>
<?php

use \CaT\Plugins\Venues\Tags\Venue\ilDB as TagsDB;

$db = new TagsDB($ilDB);
$db->updateColumn1();
?>

<#24>
<?php

$db = new \CaT\Plugins\Venues\Venues\Costs\ilDB($ilDB);
$db->update2();
?>

<#25>
<?php

$db = new \CaT\Plugins\Venues\Venues\General\ilDB($ilDB);
$db->update1();
?>

<#26>
<?php

$db = new \CaT\Plugins\Venues\Venues\Address\ilDB($ilDB);
$db->update1();
?>

<#27>
<?php

$db = new \CaT\Plugins\Venues\Venues\Service\ilDB($ilDB);
$db->update1();
?>

<#28>
<?php

$db = new \CaT\Plugins\Venues\Venues\Service\ilDB($ilDB);
$db->update2();
?>

<#29>
<?php

$db = new \CaT\Plugins\Venues\Venues\Service\ilDB($ilDB);
$db->update3();
?>

<#30>
<?php

use \CaT\Plugins\Venues\Tags\Search\ilDB as TagsDB;

$db = new TagsDB($ilDB);
$db->install();
?>

<#31>
<?php

use \CaT\Plugins\Venues\VenueAssignment\ilDB as AssignmentDB;

$db = new AssignmentDB($ilDB);
$db->update1();
?>