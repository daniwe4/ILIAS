<?php

declare(strict_types=1);

namespace CaT\Plugins\CourseCreation\Recipients;

interface DB
{
    /**
     * @param string[]
     */
    public function saveRecipients(array $recipients);

    /**
     * @return Recipient[]
     */
    public function getRecipients() : array;

    /**
     * @return string[]
     */
    public function getRecipientsForForm() : array;
}
