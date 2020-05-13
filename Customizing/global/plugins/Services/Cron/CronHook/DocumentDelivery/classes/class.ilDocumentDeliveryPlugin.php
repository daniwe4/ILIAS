<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

use CaT\Plugins\DocumentDelivery\DI;
use CaT\Plugins\DocumentDelivery\SignatureList;
use CaT\Plugins\DocumentDelivery\Documents;

class ilDocumentDeliveryPlugin extends ilCronHookPlugin
{
	use DI;

	/**
	 * @inheritDoc
	 */
	public function getPluginName()
	{
		return 'DocumentDelivery';
	}

	/**
	 * @inheritDoc
	 */
	public function getCronJobInstances()
	{
		return [];
	}

	/**
	 * @inheritDoc
	 */
	public function getCronJobInstance($a_job_id)
	{
		return null;
	}

	public function getLinkForSignatureList(int $crs_id, int $template_id) : string
	{
		/** @var SignatureList\DB $db */
		$sig_db = $this->getDIC()['signaturelist.db'];
		/** @var Documents\DB $db */
		$doc_db = $this->getDIC()['documents.db'];

		try {
			$hash = $sig_db->lookupSignatureListHashFor($crs_id, $template_id);
			/** @var SignatureList\Document $document */
			$document = $sig_db->getSignatureListFor($hash);
		} catch (\LogicException $e) {
			/** @var SignatureList\Document $document */
			$document = $sig_db->addSignatureList($crs_id, $template_id);
			$doc_db->addDocument(Documents\Document::TYPE_SIGNATURE_LIST, $document->getHash());
		}

		return $this->createLinkFromDocument($document);
	}

	protected function getPrinterForSignatureList() : SignatureList\Printer
	{
		return $this->getDIC()['signaturelist.print'];
	}

	public function printDocumentForHash(string $hash)
	{
		/** @var Documents\DB $db */
		$doc_db = $this->getDIC()['documents.db'];
		$type = $doc_db->getTypeOfDocumentHash($hash);

		switch($type) {
			case Documents\Document::TYPE_SIGNATURE_LIST:
				/** @var SignatureList\DB $db */
				$db = $this->getDIC()['signaturelist.db'];
				$document = $db->getSignatureListFor($hash);
				if (! ilObject::_exists($document->getCrsId())) {
					throw new LogicException("No existing crs");
				}
				$printer = $this->getPrinterForSignatureList();
				$printer->printListFor($document);
				break;
			default:
				throw new \LogicException('No document found for hash');
		}
	}

	protected function createLinkFromDocument(Documents\Document $document) : string
	{
		return 'print.php?client_id='.CLIENT_ID.'&file='.$document->getHash();
	}

	protected function getDIC()
	{
		global $DIC;
		return $this->getPluginDIC($this, $DIC);
	}
}