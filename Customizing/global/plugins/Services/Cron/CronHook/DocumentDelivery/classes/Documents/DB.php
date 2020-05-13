<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\Documents;

interface DB
{
	public function addDocument(string $type, string $hash);

	/**
	 * @thorws \LogicException if no type for hash was found
	 */
	public function getTypeOfDocumentHash(string $hash) : string;
}