<?php

declare(strict_types=1);

class EventData
{
    /**
     * @var int
     */
    protected $usr_id;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string[]
     */
    protected $attachments;

    public function __construct(int $usr_id, string $subject, string $message, array $attachments)
    {
        $this->usr_id = $usr_id;
        $this->subject = $subject;
        $this->message = $message;
        $this->attachments = $attachments;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function getAttachments() : array
    {
        return $this->attachments;
    }
}
