<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\SignatureList;

class ilDB implements DB
{
	const TABLE_NAME = "public_siglists";

	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritDoc
	 */
	public function addSignatureList(int $crs_id, int $template_id) : Document
	{
		$id = $this->getNextId();
		$hash = $this->getHash();

		$document = new Document(
			$id,
			$crs_id,
			$template_id,
			$hash
		);

		$values = [
			'id' => [
				'integer',
				$id
			],
			'crs_id' => [
				'integer',
				$crs_id,
			],
			'template_id' => [
				'integer',
				$template_id
			],
			'hash' => [
				'text',
				$hash
			]
		];
		$this->db->insert(self::TABLE_NAME, $values);

		return $document;
	}

	/**
	 * @inheritDoc
	 */
	public function getSignatureListFor(string $hash) : Document
	{
		$q = 'SELECT id, crs_id, template_id'.PHP_EOL
			.' FROM '. self::TABLE_NAME.PHP_EOL
			.' WHERE hash = '.$this->db->quote($hash, "text").PHP_EOL
		;

		$res = $this->db->query($q);
		if($this->db->numRows($res) == 0) {
			throw new \LogicException('no document found for hash: '.$hash);
		}

		$row = $this->db->fetchAssoc($res);
		return new Document(
			(int)$row['id'],
			(int)$row['crs_id'],
			(int)$row['template_id'],
			$hash
		);
	}

	/**
	 * @inheritDoc
	 */
	public function lookupSignatureListHashFor(int $crs_id, int $template_id) : string
	{
		$q = 'SELECT hash'.PHP_EOL
			.' FROM '. self::TABLE_NAME.PHP_EOL
			.' WHERE crs_id = '.$this->db->quote($crs_id, "integer").PHP_EOL
			.'     AND template_id = '.$template_id
		;

		$res = $this->db->query($q);
		if($this->db->numRows($res) == 0) {
			throw new \LogicException('no hash found');
		}

		$row = $this->db->fetchAssoc($res);
		return (string)$row['hash'];
	}

	protected function getNextId() : int
	{
		return (int)$this->db->nextId(self::TABLE_NAME);
	}

	protected function getHash() : string
	{
		$data = $this->getRandomString();
		return hash('sha512', $data, false);
	}

	protected function getRandomString() : string
	{
		return (string)random_bytes(random_int(100, 350));
	}
 }