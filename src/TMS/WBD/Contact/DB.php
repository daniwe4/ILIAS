<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

declare(strict_types=1);

namespace ILIAS\TMS\WBD\Contact;

use \Exception;

interface DB
{
	const CONTACT_TITLE = 'c_title';
	const CONTACT_FIRSTNAME = 'c_firstname';
	const CONTACT_LASTNAME = 'c_lastname';
	const CONTACT_PHONE = 'c_phone';
	const CONTACT_EMAIL = 'c_email';

	const ETR_CONTACT_MODE_TUTOR = 'etr_c_m_tutor';
	const ETR_CONTACT_MODE_ADMIN = 'etr_c_m_admin';
	const ETR_CONTACT_MODE_CCL = 'etr_c_m_ccl';
	const ETR_CONTACT_MODE_STATIC = 'etr_c_m_static';
	const ETR_CONTACT_MODE_NONE = 'etr_c_m_none';
	/**
	 * @return string
	 * @throws Exception
	 */
	public function eduTrackingContactMode() : string;

	/**
	 * @return array
	 * @throws Exception
	 */
	public function eduTrackingStaticContactInfo() : array;
}