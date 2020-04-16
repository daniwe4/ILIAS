<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace CaT\Plugins\BookingApprovals\Mailing;

/**
 * Mailing-templates used for this plugin.
 */
class MailTemplateConstants
{
    const MAILING_TEMPLATE_INFORM_APPROVERS = 'BR01';
    const MAILING_TEMPLATE_REQUEST_APPROVED = 'BR02';
    const MAILING_TEMPLATE_REQUEST_DECLINED = 'BR03';
    const MAILING_TEMPLATE_REQUEST_OBSOLETE = 'BR04';
    const MAILING_TEMPLATE_REQUEST_PROCESSING_ERROR = 'BR05';
}
