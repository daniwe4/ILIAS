<#1>
<?php
global $DIC;
$db = $DIC["ilDB"];
if (!$db->tableExists("cots_objects")) {
	$fields =
		array(
			'id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true
			),
			'parent_id' => array(
				'type' 		=> 'integer',
				'length' 	=> 4,
				'notnull' 	=> true
			)
		);

	$db->createTable("cots_objects", $fields);
}
?>

<#2>
<?php
global $DIC;
$db = $DIC["ilDB"];

try {
	$db->addPrimaryKey("cots_objects", ["id"]);
} catch(\PDOException $e) {
	$db->dropPrimaryKey("cots_objects");
	$db->addPrimaryKey("cots_objects", ["id"]);
}
?>

<#3>
<?php
global $DIC;
$db = $DIC["ilDB"];

if(!$db->sequenceExists("cots_objects")) {
	$db->createSequence("cots_objects");
}
?>
