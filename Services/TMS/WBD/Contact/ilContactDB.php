<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);


use CaT\Plugins\EduTracking\Purposes\WBD\Configuration;
use ILIAS\TMS\WBD\Contact\DB;

class ilContactDB implements DB
{
	/**
	 * @inheritDoc
	 */
	public function eduTrackingContactMode() : string
	{
		try {
			/** @var \ilEduTrackingPlugin $plugin */
			$plugin = \ilPluginAdmin::getPluginObjectById('xetr');
		} catch( \Exception $e) {
			return self::ETR_CONTACT_MODE_NONE;
		}

		/** @var Configuration\ilActions $actions */
		$actions = $plugin->getConfigActionsFor('WBD');
		/** @var Configuration\ConfigWBD $config */
		$config = $actions->select();
		if($config->getAvailable()) {
			switch($config->getContact()) {
				case 'course_tutor':
					return self::ETR_CONTACT_MODE_TUTOR;
				case 'course_admin':
					return self::ETR_CONTACT_MODE_ADMIN;
				case 'xccl_contact':
					return self::ETR_CONTACT_MODE_CCL;
				case 'fixed_contact':
					return self::ETR_CONTACT_MODE_STATIC;
			}
		}
		return self::ETR_CONTACT_MODE_NONE;
	}

	/**
	 * @inheritDoc
	 */
	public function eduTrackingStaticContactInfo() : array
	{
		try {
			/** @var \ilEduTrackingPlugin $plugin */
			$plugin = \ilPluginAdmin::getPluginObjectById('xetr');
		} catch( \Exception $e) {
			return [];
		}

		/** @var Configuration\ilActions $actions */
		$actions = $plugin->getConfigActionsFor('WBD');
		/** @var Configuration\ConfigWBD $config */
		$config = $actions->select();
		if($config->getContact() !== 'fixed_contact') {
			return [];
		}
		$usr = new \ilObjUser($config->getUserId());
		return [
			self::CONTACT_TITLE => $usr->getUTitle()
			,self::CONTACT_FIRSTNAME => $usr->getFirstname()
			,self::CONTACT_LASTNAME => $usr->getLastname()
			,self::CONTACT_EMAIL => $usr->getEmail()
			,self::CONTACT_PHONE => $usr->getPhoneOffice()
		];
	}
}