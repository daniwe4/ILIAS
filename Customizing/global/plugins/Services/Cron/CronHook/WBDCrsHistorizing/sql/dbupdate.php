<#1>
<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/WBDCrsHistorizing/classes/HistCases/WBDCrs.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\WBDCrsHistorizing\HistCases\WBDCrs;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->createTableFor(new WBDCrs());
?>

<#2>
<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/WBDCrsHistorizing/classes/HistCases/WBDCrs.php';
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\WBDCrsHistorizing\HistCases\WBDCrs;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->createTableFor(new WBDCrs());
?>

<#3>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/WBDCrsHistorizing/classes/Rehistorization.php';
$rh = new Rehistorization();
$rh->rehistorizeAll();
?>

<#4>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/WBDCrsHistorizing/classes/Rehistorization.php';
$rh = new Rehistorization();
$rh->rehistorizeAssignments();
?>

<#5>
<?php


use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\WBDCrsHistorizing\HistCases\WBDCrs;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new WBDCrs());
?>

<#6>
<?php


use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\WBDCrsHistorizing\HistCases\WBDCrs;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new WBDCrs());
?>