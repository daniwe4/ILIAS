<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> */

namespace ILIAS\TMS\Mailing;

/**
 * This builds content for mails in TMS, as e.g. used for
 * automatic notifications in courses.
 *
 */
interface MailContentBuilder
{

    /**
     * get instance of this with template-identifier
     *
     * @param string 	$ident
     * @return MailContentBuilder
     */
    public function withData($ident);

    /**
     * Get the template's id of this Mail.
     *
     * @return int
     */
    public function getTemplateId();

    /**
     * Get the template's identifier of this Mail.
     *
     * @return int
     */
    public function getTemplateIdentifier();

    /**
     * Get the subject of Mail with placeholders applied
     *
     * @return string
     */
    public function getSubject();


    /**
     * Gets the (HTML-)message of Mail with filled placeholders,
     * i.e.: apply all from placeholder values to template's message'.
     *
     * @return 	string
     */
    public function getMessage();

    /**
     * Gets the message of Mail with filled placeholders,
     * i.e.: apply all from placeholder values to template's message'.
     * All tags are stripped.
     *
     * @return string
     */
    public function getPlainMessage();

    /**
     * Returns pathes to images that should be embedded.
     *
     * @return 	array[]		array( array('/path/to/img/img.jpg', 'img.jpg'), ...)
     */
    public function getEmbeddedImages();

    /**
     * Returns pathes of attachments of the mail.
     *
     * @return string[]
     */
    public function getAttachments();

    /**
     *  Change style for mails according to the recipient's settings.
     *
     * @param 	Recipient 	$recipient
     * @return 	MailContentBuilder
     */
    public function withStyleFor(Recipient $recipient);

    /**
     * get instance of this with contexts
     *
     * @param MailContext[] $contexts
     * @return MailContentBuilder
     */
    public function withContexts(array $contexts);

    /**
     * get instance of this with subject
     *
     * @param string $subject
     * @return MailContentBuilder
     */
    public function withSubject(string $subject);

    /**
     * get instance of this with body
     *
     * @param string $body
     * @return MailContentBuilder
     */
    public function withBody(string $body);

    /**
     * get instance of this with template id
     *
     * @param string $tempalte_id
     * @return MailContentBuilder
     */
    public function withTemplateId(string $tempalte_id);
}
