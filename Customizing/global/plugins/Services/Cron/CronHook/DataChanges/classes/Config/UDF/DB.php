<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types = 1);

namespace CaT\Plugins\DataChanges\Config\UDF;

interface DB
{
	CONST KEY_GUTBERATEN_ID = "gutberaten_id";
	CONST KEY_ANNOUNCE_ID = "announce_id";

	/**
	 * @return UDFDefinition|null
	 */
	public function getUDFFieldIdForBWVID();
	public function saveUDFFieldIdForBWVID(int $field_id);

	/**
	 * @return UDFDefinition[]
	 */
	public function getUDFDefinitions() : array;
}