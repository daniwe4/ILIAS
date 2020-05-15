<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\COTextSearch\Settings;

interface DB
{
	public function create(int $parent_id) : Settings;
	public function deleteFor(int $id);
}