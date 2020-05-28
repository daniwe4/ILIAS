<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/**
 * career goal plugin for repository
 *
 * @author 		Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilTalentAssessmentReportPlugin extends ilRepositoryObjectPlugin
{
	public function getPluginName()
	{
		return "TalentAssessmentReport";
	}

	public function uninstallCustom()
	{
	}

	protected function beforeActivation()
	{
		parent::beforeActivation();
		global $DIC;
		$db = $DIC->database();
		// before activating, we ensure, that the type exists in the ILIAS
		// object database and that all permissions exist
		$type = $this->getId();

		if (substr($type, 0, 1) != "x") {
			throw new ilPluginException("Object plugin type must start with an x. Current type is ".$type.".");
		}

		// check whether type exists in object data, if not, create the type
		$set = $db->query("SELECT * FROM object_data ".
			" WHERE type = ".$db->quote("typ", "text").
			" AND title = ".$db->quote($type, "text"));
		if ($rec = $db->fetchAssoc($set)) {
			$t_id = $rec["obj_id"];
		} else {
			$t_id = $db->nextId("object_data");
			$db->manipulate("INSERT INTO object_data ".
				"(obj_id, type, title, description, owner, create_date, last_update) VALUES (".
				$db->quote($t_id, "integer").",".
				$db->quote("typ", "text").",".
				$db->quote($type, "text").",".
				$db->quote("Plugin ".$this->getPluginName(), "text").",".
				$db->quote(-1, "integer").",".
				$db->quote(ilUtil::now(), "timestamp").",".
				$db->quote(ilUtil::now(), "timestamp").
				")");
		}

		// add rbac operations for plugin
		// 58: copy
		$ops = array(58);

		foreach ($ops as $op) {
			// check whether type exists in object data, if not, create the type
			$set = $db->query("SELECT * FROM rbac_ta ".
				" WHERE typ_id = ".$db->quote($t_id, "integer").
				" AND ops_id = ".$db->quote($op, "integer"));
			if (!$db->fetchAssoc($set)) {
				$db->manipulate("INSERT INTO rbac_ta ".
					"(typ_id, ops_id) VALUES (".
					$db->quote($t_id, "integer").",".
					$db->quote($op, "integer").
					")");
			}
		}

		return true;
	}

	/**
	 * Get a closure to get txts from plugin.
	 *
	 * @return \Closure
	 */
	public function txtClosure()
	{
		return function ($code) {
			return $this->txt($code);
		};
	}

	/**
	 * decides if this repository plugin can be copied
	 *
	 * @return bool
	 */
	public function allowCopy()
	{
		return true;
	}
}
