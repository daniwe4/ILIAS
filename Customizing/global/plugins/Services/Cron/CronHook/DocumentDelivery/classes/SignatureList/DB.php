<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\SignatureList;

interface DB
{
	public function addSignatureList(int $crs_id, int $template_id) : Document;

	/**
	 * @thorws \LogicException if no document was found
	 */
	public function getSignatureListFor(string $hash) : Document;

	/**
	 * @thorws \LogicException if no hash was found
	 */
	public function lookupSignatureListHashFor(int $crs_id, int $template_id) : string;
}