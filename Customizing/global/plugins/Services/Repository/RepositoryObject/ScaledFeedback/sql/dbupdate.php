<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->install();
?>
<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Feedback\ilDB($ilDB);
$db->install();
?>
<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Settings\ilDB($ilDB);
$db->install();
?>
<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createPrimaryKeyForSets();
?>
<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createSequenceForSets();
?>
<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createPrimaryKeyForDimensions();
?>
<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createSequenceForDimensions();
?>
<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createPrimaryKeyForInterim();
?>
<#9>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->createSequenceForInterim();
?>
<#10>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Feedback\ilDB($ilDB);
$db->createPrimaryKeyForFeedback();
?>
<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Settings\ilDB($ilDB);
$db->createPrimaryKeyForSettings();
?>
<#12>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Settings\ilDB($ilDB);
$db->createSequenceForSettings();
?>
<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Feedback\ilDB($ilDB);
$db->update1();
?>
<#14>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Feedback\ilDB($ilDB);
$db->update2();
?>
<#15>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->update1();
?>

<#16>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/classes/class.ilObjScaledFeedback.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\ScaledFeedback\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xfbk'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjScaledFeedback();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>
<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->update2();
?>
<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Config\ilDB($ilDB);
$db->update3();
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/ScaledFeedback/vendor/autoload.php");
$db = new \CaT\Plugins\ScaledFeedback\Feedback\ilDB($ilDB);
$db->update2();
?>