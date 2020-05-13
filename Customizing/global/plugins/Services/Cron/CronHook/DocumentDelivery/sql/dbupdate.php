<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$this->db->tableExists('public_siglists')) {
	$fields = [
		'id' => [
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> true
		],
		'crs_id' => [
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> true
		],
		'template_id' => [
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> true
		],
		'hash' => [
			'type' => 'clob',
			'notnull' => true
		]
	];

	$this->db->createTable('public_siglists', $fields);
}
?>

<#2>
<?php
global $DIC;
$db = $DIC['ilDB'];

try {
	$this->db->addPrimaryKey('public_siglists', ['id']);
} catch (\PDOException $e) {
	$this->db->dropPrimaryKey('public_siglists');
	$this->db->addPrimaryKey('public_siglists', ['id']);
}
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];

if(! $db->sequenceExists('public_siglists')) {
	$db->createSequence('public_siglists');
}
?>

<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$this->db->tableExists('document_types')) {
	$fields = [
		'id' => [
			'type' 		=> 'integer',
			'length' 	=> 4,
			'notnull' 	=> true
		],
		'type' => [
			'type' 		=> 'text',
			'length' 	=> 100,
			'notnull' 	=> true
		],
		'hash' => [
			'type' => 'clob',
			'notnull' => true
		]
	];

	$this->db->createTable('document_types', $fields);
}
?>

<#5>
<?php
global $DIC;
$db = $DIC['ilDB'];

try {
	$this->db->addPrimaryKey('document_types', ['id']);
} catch (\PDOException $e) {
	$this->db->dropPrimaryKey('document_types');
	$this->db->addPrimaryKey('document_types', ['id']);
}
?>

<#6>
<?php
global $DIC;
$db = $DIC['ilDB'];

if(! $db->sequenceExists('document_types')) {
	$db->createSequence('document_types');
}
?>
