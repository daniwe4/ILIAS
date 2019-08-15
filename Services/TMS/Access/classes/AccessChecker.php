<?php

declare(strict_types=1);

class AccessChecker
{
	/**
	 * @var int
	 */
	protected $ref_id_to_check;

	/**
	 * @var ilAccess
	 */
	protected $access;

	public function __construct(int $ref_id_to_check, ilAccess $access)
	{
		$this->ref_id_to_check = $ref_id_to_check;
		$this->access = $access;
	}

	public function canRead() : bool
	{
		return $this->hasPermissionTo("read");
	}

	public function canWrite() : bool
	{
		return $this->hasPermissionTo("write");
	}

	public function canEditPermissions() : bool
	{
		return $this->hasPermissionTo("edit_permission");
	}

	public function hasPermissionTo(string $permission) : bool
	{
		return $this->access->checkAccess($permission, "", $this->ref_id_to_check);
	}
}