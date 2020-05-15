<#1>
<?php
if(!$ilDB->tableExists("rep_xlpr_data")) {
	$fields = 
	array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true
		),
		'is_online' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true
		));
	$ilDB->createTable("rep_xlpr_data", $fields);
	$ilDB->addPrimaryKey("rep_xlpr_data", array('id'));
}
?>

<#2>
<?php
if(!$ilDB->tableColumnExists("rep_xlpr_data",'target_role')) {
	$ilDB->addTableColumn("rep_xlpr_data", 'target_role', 
			array(
				'type' => 'integer', 
				'length' => 4,
				'notnull' => true,
				'default' => -1
			));
}
?>