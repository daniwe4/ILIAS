<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * everything a mail needs to know
 */
class TMSMail implements Mail
{

    /**
     * @var Recipient
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $template_ident;

    /**
     * @var MailContext[]
     */
    protected $contexts;

    /**
     * @var Attachments | null
     */
    protected $attachments;

    protected $is_freetext;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $body;

    public function __construct(
        Recipient $recipient,
        string $template_ident,
        array $contexts,
        Attachments $attachments = null,
        bool $is_freetext = false,
        string $subject = "",
        string $body = ""
    ) {
        $this->recipient = $recipient;
        $this->template_ident = $template_ident;
        $this->contexts = $contexts;
        $this->attachments = $attachments;
        $this->is_freetext = $is_freetext;
        $this->subject = $subject;
        $this->body = $body;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateIdentifier()
    {
        return $this->template_ident;
    }

    /**
     * @inheritdoc
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @inheritdoc
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    public function isFreetext() : bool
    {
        return $this->is_freetext;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getBody() : string
    {
        return $this->body;
    }
}
