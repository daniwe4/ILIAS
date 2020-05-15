<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config;

use Exception;
use ilObjectFactory;
use ilObjUser;

trait Checker
{
	public function validateUserLogin(string $login) : bool
	{
		return ilObjUser::_loginExists($login) !== false;
	}

	public function isExistingCourse(int $ref_id) : bool
	{
		try {
			$type = ilObjectFactory::getTypeByRefId($ref_id);
		} catch (Exception $e) {
			return false;
		}

		return "crs" == $type;
	}
}