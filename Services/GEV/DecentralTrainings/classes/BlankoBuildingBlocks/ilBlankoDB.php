<?php

require_once("Services/GEV/DecentralTrainings/classes/BlankoBuildingBlocks/BlankoBuildingBlock.php");

/**
 * Database handle for blanko building block informations
 */
class ilBlankoDB {
	const TABLE_NAME = "dct_blanko_bb_infos";

	public function __construct() {
		global $ilDB;

		$this->gDB = $ilDB;
	}

	public function install() {
		$this->createTable();
	}

	protected function createTable() {
		if(!$this->getDB()->tableExists(self::TABLE_NAME)) {
			$fields = 
				array('bb_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'crs_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> false
					),
					'request_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'content' => array(
						'type' 		=> 'clob',
						'notnull' 	=> true
					),
					'target' => array(
						'type' 		=> 'clob',
						'notnull' 	=> false
					)
				);

			$this->getDB()->createTable(self::TABLE_NAME, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_NAME, array("bb_id"));
		}
	}

	public function save(BlankoBuildingBlock $blanko_block) {
		$values = array
				( "bb_id" => array("integer", $blanko_block->getBbId())
				, "crs_id" => array("float", $blanko_block->getCrsId())
				, "request_id" => array("float", $blanko_block->getRequestId())
				, "content" => array("text", $blanko_block->getContent())
				, "target" => array("text", $blanko_block->getTarget())
				);

		$this->getDB()->insert(self::TABLE_NAME, $values);
	}

	/**
	 * Get all blanko block informations for request id
	 *
	 * @param int 	$bb_id 			id of course building block
	 * @param int 	$request_id 	id of the open request
	 *
	 * @return array<int, BlankoBuildingBlock>
	 */
	public function getBlankoBuldingBlockForRequest($bb_id, $request_id) {
		$query = $this->getSelectStatement();
		$query .= " WHERE bb_id = ".$this->getDB()->quote($bb_id, "integer")."\n"
				 ."     AND request_id = ".$this->getDB()->quote($request_id, "integer")."\n";

		$res = $this->getDB()->query($query);

		$ret = null;

		while($row = $this->getDB()->fetchAssoc($res)) {
			$ret = new BlankoBuildingBlock($row["bb_id"]
					, $row["crs_id"]
					, $row["request_id"]
					, $row["content"]
					, $row["target"]
				);
		}

		return $ret;
	}

	/**
	 * Get all blanko block informations for crs id
	 *
	 * @param int 	$bb_id 			id of course building block
	 * @param int 	$crs_id 	ref id of the crs
	 *
	 * @return array<int, BlankoBuildingBlock>
	 */
	public function getBlankoBuldingBlockForCourse($bb_id, $crs_id) {
		$query = $this->getSelectStatement();
		$query .= " WHERE bb_id = ".$this->getDB()->quote($bb_id, "integer")."\n"
				 ."     AND crs_id = ".$this->getDB()->quote($crs_id, "integer")."\n";

		$res = $this->getDB()->query($query);

		$ret = null;

		while($row = $this->getDB()->fetchAssoc($res)) {
			$ret = new BlankoBuildingBlock($row["bb_id"]
					, $row["crs_id"]
					, $row["request_id"]
					, $row["content"]
					, $row["target"]
				);
		}

		return $ret;
	}

	/**
	 * Delete blanko block information if course block is deleted
	 *
	 * @param int 	$crs_bb 	id of the course block
	 */
	public function deleteByCrsBB($crs_bb) {
		$query = "DELETE FROM ".self::TABLE_NAME."\n"
			    ." WHERE bb_id = ".$this->getDB()->quote($crs_bb);

		$this->getDB()->manipulate($query);
	}

	/**
	 * Add the crs ref id to block informations
	 *
	 * @param int 	$request_id 	id of the creation request
	 * @param int 	$crs_id 		crs ref id
	 */
	public function moveToCrsId($request_id, $crs_id) {
		$query = "UPDATE ".self::TABLE_NAME."\n"
				." SET crs_id = ".$this->getDB()->quote($crs_id)."\n"
				." WHERE request_id = ".$this->getDB()->quote($request_id);

		$this->getDB()->manipulate($query);
	}

	protected function getSelectStatement() {
		return "SELECT bb_id, crs_id, request_id, content, target\n"
			  ." FROM ".self::TABLE_NAME."\n";
	}

	protected function getDB() {
		return $this->gDB;
	}
}