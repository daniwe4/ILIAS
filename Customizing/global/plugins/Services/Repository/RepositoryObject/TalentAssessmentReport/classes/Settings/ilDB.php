<?php

namespace CaT\Plugins\TalentAssessmentReport\Settings;

/**
 * Defines working with settings in database
 *
 * @author 	Stefan Hecken 	<stefan.hecken@concepts-anf-training.de>
 */
class ilDB implements DB
{
	const TABLE_NAME = "xtar_settings";
	/**
	 * @var \ilDBInterface
	 */
	protected $db;

	public function __construct(\ilDBInterface $db)
	{
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function install()
	{
		$this->createTable();
	}

	/**
	 * @inheritdoc
	 */
	public function create(int $obj_id, bool $is_admin, bool $is_online)
	{
		$settings = new TalentAssessmentReport($obj_id, $is_admin, $is_online);

		$values = array("obj_id" => array("integer", $settings->getObjId()),
				"is_admin" => array("integer", $settings->getIsAdmin()),
				"is_online" => array("integer", $settings->getIsOnline())
			);

		$this->getDB()->insert(self::TABLE_NAME, $values);

		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function update(TalentAssessmentReport $settings)
	{
		$where = array("obj_id" => array("integer", $settings->getObjId()));

		$values = array("is_admin" => array("integer", $settings->getIsAdmin()),
				"is_online" => array("integer", $settings->getIsOnline())
			);

		$this->getDB()->update(self::TABLE_NAME, $values, $where);
	}

	/**
	 * @inheritdoc
	 */
	public function selectFor(int $obj_id)
	{
		$query = "SELECT obj_id, is_admin, is_online\n"
				." FROM ".self::TABLE_NAME."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$res = $this->getDB()->query($query);

		if ($this->getDB()->numRows($res) == 0) {
			throw new \LogicException(__METHOD__." no settings found for ". $obj_id);
		}

		$row = $this->getDB()->fetchAssoc($res);

		$settings = new TalentAssessmentReport((int)$row["obj_id"], (bool)$row["is_admin"], (bool)$row["is_online"]);

		return $settings;
	}

	/**
	 * @inheritdoc
	 */
	public function deleteFor(int $obj_id)
	{
		$query = "DELETE FROM ".self::TABLE_NAME."\n"
				." WHERE obj_id = ".$this->getDB()->quote($obj_id, "integer");

		$this->getDB()->manipulate($query);
	}

	/**
	 * Create basic table for settings
	 *
	 * @return null
	 */
	protected function createTable()
	{
		if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
			$fields =
				array('obj_id' => array(
						'type' 		=> 'integer',
						'length' 	=> 4,
						'notnull' 	=> true
					),
					'is_admin' => array(
						'type' 		=> 'float',
						'notnull' 	=> true
					),
					'is_online' => array(
						'type' 		=> 'integer',
						'length'	=> 4,
						'notnull' 	=> true
					)
				);

			$this->getDB()->createTable(self::TABLE_NAME, $fields);
			$this->getDB()->addPrimaryKey(self::TABLE_NAME, array("obj_id"));
		}
	}

	/**
	 * Get the database handle
	 *
	 * @throws \Exception
	 *
	 * @return \ilDBInterface
	 */
	protected function getDB()
	{
		if (!$this->db) {
			throw new \Exception("no Database");
		}
		return $this->db;
	}
}
