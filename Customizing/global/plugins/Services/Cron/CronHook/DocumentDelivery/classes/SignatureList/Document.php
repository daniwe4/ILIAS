<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

namespace CaT\Plugins\DocumentDelivery\SignatureList;

use CaT\Plugins\DocumentDelivery\Documents\Document as BaseDocuments;

class Document implements BaseDocuments
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var int
	 */
	protected $crs_id;

	/**
	 * @var int
	 */
	protected $template_id;

	/**
	 * @var string
	 */
	protected $hash;

	public function __construct(int $id, int $crs_id, int $template_id, string $hash)
	{
		$this->id = $id;
		$this->crs_id = $crs_id;
		$this->template_id = $template_id;
		$this->hash = $hash;
	}

	public function getId() : int
	{
		return $this->id;
	}

	public function getCrsId() : int
	{
		return $this->crs_id;
	}

	public function getTemplateId() : int
	{
		return $this->template_id;
	}

	public function getHash() : string
	{
		return $this->hash;
	}
}