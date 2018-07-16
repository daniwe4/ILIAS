<#1>
<?php
	if (!$ilDB->tableColumnExists('event', 'tutor_source')){
		$ilDB->addTableColumn('event', 'tutor_source', array(
			"type" => "integer",
			'length' => 1,
			"notnull" => true,
			"default" => 0
		));
	}
?>
<#2>
<?php
	$table_name = 'event_tutors';
	if (!$ilDB->tableExists($table_name)){
		$fields = array(
			'id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),
			'obj_id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),
			'usr_id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true,
				'default' 	=> -1
			),

		);
		$ilDB->createTable($table_name, $fields);
		$ilDB->createSequence($table_name);
	}
?>
<#3>
<?php
	$ilDB->addPrimaryKey('event_tutors', array("id"));
?>
<#4>
<?php
	// cat-tms-patch start
	$ilDB->insert(
		'mail_tpl_ctx',
		array(
			'id' => array('text', 'crs_context_invitation'),
			'component' => array('text', 'Modules/Course'),
			'class' => array('text', 'ilCourseMailTemplateInvitationContext')
		)
	);
	// cat-tms-patch end
?>
<#5>
<?php
	// cat-tms-patch start
	$ilDB->insert(
		'mail_tpl_ctx',
		array(
			'id' => array('text', 'crs_context_automatic'),
			'component' => array('text', 'Modules/Course'),
			'class' => array('text', 'ilCourseMailTemplateAutomaticContext')
		)
	);
	// cat-tms-patch end
?>
<#6>
<?php
	// cat-tms-patch start
	if (!$ilDB->tableColumnExists("event_appointment", "days_offset"))
	{
		$ilDB->addTableColumn("event_appointment", "days_offset", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4,
			"default" => null));
	}
	// cat-tms-patch end
?>
<#7>
<?php
	global $DIC;
	require_once("Services/Tree/classes/class.ilTree.php");
	$tree = new ilTree(0);
	require_once("Services/Object/classes/class.ilObjectDataCache.php");
	$cache = new ilObjectDataCache();
	$provider_db = new CaT\Ente\ILIAS\ilProviderDB($DIC->database(), $tree, $cache);
	$provider_db->createTables();
?>
<#8>
<?php

global $DIC;
$db = $DIC->database();
require_once("Services/Object/classes/class.ilObjectDataCache.php");
$DIC["ilObjDataCache"] = new ilObjectDataCache();
require_once("Services/Tree/classes/class.ilTree.php");
$DIC["tree"] = new ilTree(-1);

require_once("Services/TMS/classes/class.ilTMSAppEventListener.php");
require_once("Services/Object/classes/class.ilObject.php");
$query = $db->query("SELECT obj_id FROM object_data WHERE type = 'crs'");
while($res = $db->fetchAssoc($query)) {
	$obj = new ilObject();
	$obj->setId($res["obj_id"]);
	ilTMSAppEventListener::createUnboundCourseProvider($obj);
}
?>
<#9>
<?php
if( !$ilDB->tableExists('crs_copy_mappings') )
{
	$ilDB->createTable('crs_copy_mappings', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'source_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('crs_copy_mappings', array('obj_id', 'source_id'));
}
?>
<#10>
<?php
require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
global $DIC;
$ilDB = $DIC->database();
$db = new ilTMSMailingLogsDB($ilDB);
$db->createTable();
?>
<#11>
<?php
require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
global $DIC;
$ilDB = $DIC->database();
$db = new ilTMSMailingLogsDB($ilDB);
$db->createSequence();
?>
<#12>
<?php
require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
global $DIC;
$ilDB = $DIC->database();
$db = new ilTMSMailingLogsDB($ilDB);
$db->createPrimaryKey();
?>
<#13>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createTable();
?>
<#14>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createPrimaryKey();
?>
<#15>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createSequence();
?>
<#16>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
global $DIC;
$db = new Schedule($DIC->database());
$db->createParamsTable();
?>
<#17>
<?php
require_once("Services/TMS/ScheduledEvents/classes/Schedule.php");
$db = new Schedule($ilDB);
$db->createPrimaryKeyForParams();
?>
<#18>
<?php
if( !$ilDB->tableExists('tms_role_settings') )
{
	$ilDB->createTable('tms_role_settings', array(
		'role_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'hide_breadcrumb' => array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0
		),
		'hide_menu_tree' => array(
			'type' => 'integer',
			'length' => 1,
			'default' => 0
		)
	));
}
?>
<#19>
<?php
if($ilDB->tableExists('tms_role_settings') )
{
	$ilDB->addPrimaryKey('tms_role_settings', array('role_id'));
}
?>
<#20>
<?php
global $DIC;
$role_root_folder = 8;

require_once("Services/TMS/Roles/classes/class.ilTMSRolesDB.php");
$tms_settings_db = new ilTMSRolesDB($ilDB);
$query = "SELECT rol_id FROM rbac_fa WHERE parent = $role_root_folder AND assign='y'";
$res = $ilDB->query($query);
while($row = $ilDB->fetchAssoc($res)) {
	$tms_settings = $tms_settings_db->selectFor((int)$row["rol_id"]);
	$tms_settings_db->update($tms_settings);
}
?>
<#21>
<?php
	global $DIC;
	require_once("Services/Tree/classes/class.ilTree.php");
	$tree = new ilTree(0);
	require_once("Services/Object/classes/class.ilObjectDataCache.php");
	$cache = new ilObjectDataCache();
	$provider_db = new CaT\Ente\ILIAS\ilProviderDB($DIC->database(), $tree, $cache);
	$provider_db->createTables();
?>
<#22>
<?php
require_once("Services/Tree/classes/class.ilTree.php");
$tree = new ilTree(0);
require_once("Services/Object/classes/class.ilObjectDataCache.php");
$cache = new ilObjectDataCache();
require_once("Services/Object/classes/class.ilObject.php");

$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $tree, $cache);
$query = "SELECT od.obj_id FROM object_data od JOIN object_reference oref ON oref.obj_id = od.obj_id WHERE od.type = 'crs' AND oref.deleted IS NULL";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
	$obj = new ilObject();
	$obj->setId($row["obj_id"]);
	foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
		$provider_db->update($provider);
	}
}
?>
<#23>
<?php
if($ilDB->tableColumnExists('usr_data', 'email')) {
	$field = array(
		"type" => "text",
		"length" => 140,
		"notnull" => false
	);
	$ilDB->modifyTableColumn('usr_data', 'email', $field);
}
?>
<#24>
<?php
if( !$ilDB->tableExists('copy_mappings') )
{
	$ilDB->createTable('copy_mappings', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'source_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('copy_mappings', array('obj_id', 'source_id'));
}
?>
<#25>
<?php
// anything withing ccm not in cm
$q = 'SELECT ccm.obj_id, ccm.source_id FROM crs_copy_mappings ccm'
	.'	LEFT JOIN copy_mappings cm'
	.'		ON ccm.obj_id = cm.obj_id'
	.'			AND ccm.source_id = cm.source_id'
	.'	WHERE cm.obj_id IS NULL';
$res = $ilDB->query($q);
while($rec = $ilDB->fetchAssoc($res)) {
	$ilDB->insert('copy_mappings',
		['obj_id' => ['integer',$rec['obj_id']]
		,'source_id' => ['integer',$rec['source_id']]]);
}
?>
<#26>
<?php
$ilDB->dropTable('crs_copy_mappings');
?>
<#27>
<?php
if( !$ilDB->tableExists('tms_cat_settings') )
{
	$ilDB->createTable('tms_cat_settings', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'show_in_cockpit' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		)
	));

	$ilDB->addPrimaryKey('tms_cat_settings', array('obj_id'));
}
?>

<#28>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#29>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#30>
<?php
require_once("Services/TMS/Mailing/classes/class.ilTMSMailingLogsDB.php");
global $DIC;
$ilDB = $DIC->database();
$db = new ilTMSMailingLogsDB($ilDB);
$db->update1();
?>

<#31>
<?php

$query = "UPDATE il_orgu_positions SET title = 'Mitarbeiter' WHERE title = 'Employees'";
$ilDB->manipulate($query);
$query = "UPDATE il_orgu_positions SET title = 'Vorgesetzte' WHERE title = 'Superiors'";
$ilDB->manipulate($query);
?>

<#32>
<?php
$query = "SELECT obj_id FROM object_data WHERE object_data.title LIKE 'il_orgu_%' AND object_data.type = 'role'";
$res = $ilDB->query($query);
$role_ids = [];
while($row = $ilDB->fetchAssoc($res)) {
	$role_ids[] = $row['obj_id'];
}
$query = "DELETE FROM role_data WHERE ".$ilDB->in('role_id', $role_ids, false, 'integer');
$ilDB->manipulate($query);
$query = "DELETE FROM rbac_fa WHERE ".$ilDB->in('rol_id', $role_ids, false, 'integer');
$ilDB->manipulate($query);
$query = "DELETE FROM rbac_pa WHERE ".$ilDB->in('rol_id', $role_ids, false, 'integer');
$ilDB->manipulate($query);
$query = "DELETE FROM rbac_ua WHERE ".$ilDB->in('rol_id', $role_ids, false, 'integer');
$ilDB->manipulate($query);
$query = "DELETE FROM object_data WHERE ".$ilDB->in('obj_id', $role_ids, false, 'integer');
$ilDB->manipulate($query);
?>