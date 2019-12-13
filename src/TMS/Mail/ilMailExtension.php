<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> */

namespace ILIAS\TMS\Mail;

trait ilMailExtension
{
    protected function raiseMailSendEvent(
        string $context_id,
        array $context_params,
        string $recipients,
        string $subject,
        string $message,
        $attachments
    ) {
        global $DIC;
        /** @var \ilAppEventHandler $event_handler */
        $event_handler = $DIC['ilAppEventHandler'];

        $parameter = [
            'context_id' => $context_id,
            'context_params' => $context_params,
            'recipients' => $recipients,
            'subject' => $subject,
            'message' => $message,
            'attachments' => $attachments
        ];
        $event_handler->raise("Services/Mail", 'mailSend', $parameter);
    }
}
