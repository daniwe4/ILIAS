<?php

namespace CaT\Plugins\TalentAssessment\Observer;

class ilDB implements DB
{
	/**
	 * @var rbacadmin
	 */
	protected $g_rbacadmin;

	public function __construct($db)
	{
		global $DIC;

		$this->g_rbacadmin = $DIC->rbac()->admin();
		$this->db = $db;
	}

	/**
	 * @inheritdoc
	 */
	public function createLocalRoleTemplate($tpl_title, $tpl_description)
	{
		include_once("./Services/AccessControl/classes/class.ilObjRoleTemplate.php");
		$roltObj = new \ilObjRoleTemplate();
		$roltObj->setTitle($tpl_title);
		$roltObj->setDescription($tpl_description);
		$roltObj->create();
	}

	/**
	 * @inheritdoc
	 */
	public function getRoltId($tpl_title)
	{
		$query = "SELECT obj_id FROM object_data ".
			" WHERE type = 'rolt' AND title = ".$this->db->quote($tpl_title, "text");

		$res = $this->db->query($query);
		$res = $this->db->fetchAssoc($res);

		return $res["obj_id"];
	}

	public function setRoleFolder($tpl_title, $rolf_ref_id)
	{
		$tpl_object = $this->getRoleTemplateObject($tpl_title);
		$this->g_rbacadmin->assignRoleToFolder($tpl_object->getId(), $rolf_ref_id, 'n');
		$this->g_rbacadmin->setProtected($rolf_ref_id, $tpl_object->getId(), 'y');
	}

	public function setDefaultPermissions($tpl_title, $rolf_ref_id, array $permissions)
	{
		$tpl_object = $this->getRoleTemplateObject($tpl_title);
		$this->g_rbacadmin->setRolePermission($tpl_object->getId(), "xtas", \ilRbacReview::_getOperationIdsByName($permissions), $rolf_ref_id);
	}

	protected function getRoleTemplateObject($tpl_title)
	{
		$obj_id = \ilObject::_getIdsForTitle($tpl_title, "rolt");
		$tpl_object = \ ilObjectFactory::getInstanceByObjId((int)$obj_id[0]);

		return $tpl_object;
	}
}
