<#1>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->createTableFor(new HistCourse());
?>

<#2>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->createTableFor(new HistCourse());
?>

<#3>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->createTableFor(new HistUserCourse());
?>

<#4>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->createTableFor(new HistUserCourse());
?>

<#5>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeCourseData();
HistoryLoadingUtilities::getInstance()->historizeCrsParticipations();
?>

<#6>
<?php
;
?>

<#7>
<?php
;
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
;
?>

<#12>
<?php
;
?>

<#13>
<?php
;
?>

<#14>
<?php
;
?>

<#15>
<?php
;
?>

<#16>
<?php
;
?>

<#17>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#18>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#19>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();
$hist->historize();
?>

<#20>
<?php
;
?>

<#21>
<?php
;
?>

<#22>
<?php
;
?>

<#23>
<?php
;
?>

<#24>
<?php
;
?>

<#25>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#26>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#27>
<?php
global $DIC;
$evt = $DIC['ilAppEventHandler'];

$hc = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();

foreach ($hc->getAllCourses() as $crs) {
    $crs_id = $crs->getId();
    foreach ($hc->getAllChildrenOfByType($crs->getRefId(), 'xcmb') as $course_member) {
        if ($course_member->getSettings()->getClosed() === true) {
            $last_edited = [];
            foreach ($course_member->getActions()->getMemberWithSavedLPSatus() as $member) {
                $last_edited[] = $member->getLastEdited()->get(IL_CAL_DATE);
            }
            if (rsort($last_edited) && count($last_edited) > 0) {
                $finalized_date = array_shift($last_edited);
                $evt->raise(
                    'Modules/Course',
                    'memberlist_finalized',
                    ['finalized_date' => $finalized_date,
                    'crs_obj_id' => (int) $crs_id]
                );
            }
        }
    }
}
?>

<#28>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();
$hist->historize();
?>

<#29>
<?php
global $DIC;
$db = $DIC['ilDB'];

use CaT\Plugins\UserCourseHistorizing\HistCases as HC;

$hist_course = new HC\HistCourse();
foreach ($hist_course->payloadFields() as $field) {
    if ($hist_course->typeOfField($field) === HC\HistCourse::HIST_TYPE_STRING) {
        $db->modifyTableColumn(
            'hhd_crs',
            $field,
            ["type" => "text", "length" => 100]
        );
        $db->modifyTableColumn(
            'hbuf_crs',
            $field,
            ["type" => "text", "length" => 100]
        );
        $db->modifyTableColumn(
            'hst_crs',
            $field,
            ["type" => "text", "length" => 100]
        );
    }
}
$hist_usr_course = new HC\HistUserCourse();
foreach ($hist_usr_course->payloadFields() as $field) {
    if ($hist_usr_course->typeOfField($field) === HC\HistUserCourse::HIST_TYPE_STRING) {
        $db->modifyTableColumn(
            'hhd_usrcrs',
            $field,
            ["type" => "text", "length" => 100]
        );
        $db->modifyTableColumn(
            'hbuf_usrcrs',
            $field,
            ["type" => "text", "length" => 100]
        );
        $db->modifyTableColumn(
            'hst_usrcrs',
            $field,
            ["type" => "text", "length" => 100]
        );
    }
}
?>

<#30>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#31>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#32>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();
foreach ($hist->getAllCourses() as $crs) {
    $hist->accomodation($crs);
}
?>

<#33>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#34>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#35>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();
foreach ($hist->getAllCourses() as $crs) {
    $hist->courseAccounting($crs);
}
?>

<#36>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#37>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#38>
<?php
global $DIC;
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\RehistorizeBookingStatus($ilDB, $DIC->rbac()->review());
$hist->run();
?>

<#39>
<?php
global $DIC;
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\RehistorizeBookingModalities($ilDB, $DIC['tree'], $DIC["objDefinition"]);
$hist->run();
?>

<#40>
<?php
global $DIC;
$DIC['ilDB']->addIndex('hhd_usrcrs', ['usr_id'], 'ius');
?>

<#41>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#42>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#43>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeCrsMembersData();
?>

<#44>
<?php
;
?>

<#45>
<?php
;
?>

<#46>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#47>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/HistCases/HistCourse.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#48>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeEduTrackingData();
?>

<#49>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeCourseData();
?>
<#50>
<?php
;
?>

<#51>
<?php
;
?>

<#52>
<?php
/**
 * this updatestep is due to a bug, which overwrote all booking and participation dates. It did not occur before 2018-10-01.
 */
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/ResetBookingDatesToFirstValue.php';
global $DIC;
$c = new \ResetBookingDatesToFirstValue($DIC['ilDB']);
$c->resetAfter(\DateTime::createFromFormat('Y-m-d', '2018-10-01'));
?>
<#53>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#54>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>
<#55>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistSessionCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->createTableFor(new HistSessionCourse());
?>

<#56>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistSessionCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->createTableFor(new HistSessionCourse());
?>

<#57>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeSessions();
?>

<#58>
<?php
global $DIC;
$db = $DIC['ilDB'];
$q = "UPDATE hhd_usrcrs SET booking_status = 'participant' WHERE booking_status = 'approval_approved'";
$db->manipulate($q);
?>

<#59>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#60>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#61>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeCourseData();
?>

<#62>
<?php
global $DIC;
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\RehistorizeParticipationStatus($DIC["ilDB"], $DIC["rbacreview"]);
$hist->run();
?>

<#63>
<?php
;
?>

<#64>
<?php
;
?>

<#65>
<?php
;
?>

<#66>
<?php
;
?>

<#67>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#68>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#69>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#70>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#71>
<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
HistoryLoadingUtilities::getInstance()->historizeCopySettings();

?>

<#72>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#73>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#74>
<?php
    require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/scripts/HistoryLoadingUtilities.php';
    HistoryLoadingUtilities::getInstance()->historizeEduTrackingData();
?>

<#75>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistCourse());
?>

<#76>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistCourse());
?>

<#77>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\ilHistorizeExistingObjects();
foreach ($hist->getAllCourses() as $crs) {
    $hist->accomodation($crs);
}
?>

<#78>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->BufferTableManager()->updateTableFor(new HistUserCourse());
?>

<#79>
<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';

use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;

global $DIC;
$t_f = new Mysql\MysqlStorageFactory(new ILIAS\IliasSql($DIC['ilDB']));
$t_f->StorageTableManager()->updateTableFor(new HistUserCourse());
?>

<#80>
<?php
global $DIC;
require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/libs/vendor/autoload.php';
$hist = new CaT\Plugins\UserCourseHistorizing\RehistorizeLocalRoles($DIC["ilDB"], $DIC["ilAppEventHandler"]);
$hist->run();
?>

<#81>
<?php
global $DIC;
$db = $DIC["ilDB"];
if ($db->tableColumnExists("hbuf_crs", "title")) {
    $db->modifyTableColumn("hbuf_crs", 'title', array(
        "type" => "text",
        "length" => 255,
        "notnull" => false
    ));
}
?>

<#82>
<?php
global $DIC;
$db = $DIC["ilDB"];
if ($db->tableColumnExists("hst_crs", "title")) {
    $db->modifyTableColumn("hst_crs", 'title', array(
        "type" => "text",
        "length" => 255,
        "notnull" => false
    ));
}
?>

<#83>
<?php
global $DIC;
$db = $DIC["ilDB"];
if ($db->tableColumnExists("hhd_crs", "title")) {
    $db->modifyTableColumn("hhd_crs", 'title', array(
        "type" => "text",
        "length" => 255,
        "notnull" => false
    ));
}
?>

<#84>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->indexExistsByFields('hbuf_usrcrs', ['usr_id'])) {
    $db->addIndex('hbuf_usrcrs', ['usr_id'], 'usr');
}
?>

<#85>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->indexExistsByFields('hbuf_usrcrs', ['crs_id'])) {
    $db->addIndex('hbuf_usrcrs', ['crs_id'], 'crs');
}
?>

<#86>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableExists('hbuf_usrcrs_roles')) {
    if (!$db->indexExistsByFields('hbuf_usrcrs_roles', ['list_id'])) {
        $db->addIndex('hbuf_usrcrs_roles', ['list_id'], 'lis');
    }
}
?>
