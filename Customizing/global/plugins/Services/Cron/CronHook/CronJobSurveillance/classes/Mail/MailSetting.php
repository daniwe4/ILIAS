<?php
namespace CaT\Plugins\CronJobSurveillance\Mail;

/**
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class MailSetting
{

    /**
     * @var 	string
     */
    protected $mail_address;


    public function __construct(string $mail_address)
    {
        $this->mail_address = $mail_address;
    }

    /**
     * @return 	string
     */
    public function getRecipientAddress()
    {
        return $this->mail_address;
    }
}
