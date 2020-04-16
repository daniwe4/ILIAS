<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace CaT\Plugins\CourseCreation;

use ILIAS\TMS\CourseCreation\Request;

/**
 * Sends mail for the given request.
 */
interface SendMails
{
    /**
     * Sends mails for successfull creation request.
     *
     * @param	Request
     * @return	void
     */
    public function sendSuccessMails(Request $request);

    /**
     * Sends mails for successfull creation request.
     *
     * @param	Request		$request
     * @param	\Exception	$exception
     * @return	void
     */
    public function sendFailMails(Request $request, \Exception $exception);

    /**
     * Sends mails for abortion of creation request.
     *
     * @param	Request
     * @return	void
     */
    public function sendAbortMails(Request $request);
}
