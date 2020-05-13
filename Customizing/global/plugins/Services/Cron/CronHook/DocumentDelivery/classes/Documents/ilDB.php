<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\Documents;

class ilDB implements DB
{
	const TABLE_NAME = 'document_types';

	private static $valid_types = [
		Document::TYPE_SIGNATURE_LIST
	];

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	/**
	 * ilDB constructor.
	 * @param \ilDBInterface $db
	 */
	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	public function addDocument(string $type, string $hash)
	{
		if(! in_array($type, self::$valid_types)) {
			throw new \LogicException("Added type ".$type." is not valid to create documents");
		}

		$id = $this->getNextId();
		$values = [
			'id' => [
				'integer',
				$id
			],
			'type' => [
				'text',
				$type
			],
			'hash' => [
				'text',
				$hash
			]
		];

		$this->db->insert(self::TABLE_NAME, $values);
	}

	/**
	 * @thorws \LogicException if no typefor hash was found
	 */
	public function getTypeOfDocumentHash(string $hash) : string
	{
		$q = 'SELECT type'.PHP_EOL
			.' FROM '.self::TABLE_NAME.PHP_EOL
			.' WHERE hash = '.$this->db->quote($hash, 'text')
		;

		$res = $this->db->query($q);
		if($this->db->numRows($res) == 0) {
			throw new \LogicException('no_type_found');
		}

		$row = $this->db->fetchAssoc($res);
		return (string)$row['type'];
	}

	protected function getNextId() : int
	{
		return (int)$this->db->nextId(self::TABLE_NAME);
	}
}