<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

namespace CaT\Plugins\DataChanges\Config;

use ilCertificateCron;
use ilCertificateQueueRepository;
use ilCertificateTemplateRepository;
use ilCertificateTypeClassMap;
use ilCourseParticipants;
use ilLogger;
use ilObjectFactory;
use ilObjUser;
use ilSetting;
use ilUserAutoComplete;

class DataChangeHelper
{
	/**
	 * @var ilLogger
	 */
	protected $log;

	/**
	 * @var ilUserAutoComplete
	 */
	protected $user_auto_complete;

	/**
	 * @var ilCertificateTemplateRepository
	 */
	protected $certificate_template_repository;

	/**
	 * @var ilCertificateTypeClassMap
	 */
	protected $certificate_type_class_map;

	/**
	 * @var ilCertificateQueueRepository
	 */
	protected $certificate_queue_repository;

	/**
	 * @var ilSetting
	 */
	protected $certificate_settings;

	/**
	 * @var ilCertificateCron
	 */
	protected $certificate_cron;

	public function __construct(
		ilLogger $log,
		ilUserAutoComplete $user_auto_complete,
		ilCertificateTemplateRepository $certificate_template_repository,
		ilCertificateTypeClassMap $certificate_type_class_map,
		ilCertificateQueueRepository $certificate_queue_repository,
		ilSetting $certificate_settings,
		ilCertificateCron $certificate_cron
	) {
		$this->log = $log;
		$this->user_auto_complete = $user_auto_complete;
		$this->certificate_template_repository = $certificate_template_repository;
		$this->certificate_type_class_map = $certificate_type_class_map;
		$this->certificate_queue_repository = $certificate_queue_repository;
		$this->certificate_settings = $certificate_settings;
		$this->certificate_cron = $certificate_cron;
	}

	public function getUserIdByLogin(string $login) : int
	{
		return (int)ilObjUser::getUserIdByLogin($login);
	}

	public function isCourseStarted(int $ref_id) : bool
	{
		$crs = ilObjectFactory::getInstanceByRefId($ref_id);
		return time() > $crs->getCourseStart()->get(IL_CAL_UNIX);
	}

	public function isMember(int $user_id, int $crs_ref_id) : bool
	{
		$obj = ilCourseParticipants::getInstance($crs_ref_id);
		return $obj->isMember($user_id);
	}

	public function hasPassed(int $user_id, int $crs_id) : bool
	{
		return ilCourseParticipants::_hasPassed($crs_id, $user_id);
	}

	public function userfieldAutocomplete()
	{
		$this->user_auto_complete->setSearchFields(['login', 'firstname', 'lastname', 'email']);
		$this->user_auto_complete->enableFieldSearchableCheck(false);
		$this->user_auto_complete->setMoreLinkAvailable(true);
		if (($_REQUEST['fetchall'])) {
			$this->user_auto_complete->setLimit(ilUserAutoComplete::MAX_ENTRIES);
		}
		echo $this->user_auto_complete->getList($_REQUEST['term']);
		exit;
	}

	public function isCertificateActivated(int $crs_id) : bool
	{
		try {
			$this->certificate_template_repository->fetchCurrentlyActiveCertificate($crs_id);
		} catch (\Exception $e) {
			return false;
		}

		return true;
	}

	public function updateUserCertificateForCourse(int $user_id, int $crs_id)
	{
		$type = \ilObject::_lookupType($crs_id);
		if ($this->certificate_type_class_map->typeExistsInMap($type)) {
			try {
				$template = $this->certificate_template_repository->fetchCurrentlyActiveCertificate(
					$crs_id
				);

				if (true == $template->isCurrentlyActive()) {
					$this->processEntry(
						$type,
						$crs_id,
						$user_id,
						$template
					);
				}
			} catch (\ilException $exception) {
				$this->log->warning($exception->getMessage());
			}
		}
	}

	private function processEntry(
		string $type,
		int $crs_id,
		int $user_id,
		\ilCertificateTemplate $template
	) {
		$className = $this->certificate_type_class_map->getPlaceHolderClassNameByType($type);

		$entry = new \ilCertificateQueueEntry(
			$crs_id,
			$user_id,
			$className,
			\ilCronConstants::IN_PROGRESS,
			(int)$template->getId(),
			time()
		);

		$mode = $this->certificate_settings->get(
			'persistent_certificate_mode',
			'persistent_certificate_mode_cron'
		);

		if ($mode === 'persistent_certificate_mode_instant') {
			$this->certificate_cron->init();
			$this->certificate_cron->processEntry(0, $entry, array());
			return;
		}

		$this->certificate_queue_repository->addToQueue($entry);
	}
}