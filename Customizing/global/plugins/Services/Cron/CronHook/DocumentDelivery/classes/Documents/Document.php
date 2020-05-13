<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\Documents;

interface Document
{
	const TYPE_SIGNATURE_LIST = 'signature_list';

	public function getHash();
}