<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */

namespace CaT\Plugins\CourseCreation;

use ILIAS\TMS\CourseCreation\Request;

/**
 * Does not send mails for the given request.
 */
class NullSendMails implements SendMails
{
    /**
     * @inheritdocs
     */
    public function sendSuccessMails(Request $request)
    {
    }

    /**
     * @inheritdocs
     */
    public function sendFailMails(Request $request, \Exception $exception)
    {
    }

    /**
     * @inheritdocs
     */
    public function sendAbortMails(Request $request)
    {
    }
}
