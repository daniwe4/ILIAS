<?php

namespace CaT\Plugins\ScheduledEvents\Mail;

/**
 * Send mail to admins (when a task fails)
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class Mailer
{

    /**
     * @var string
     */
    protected $recipient_address;

    /**
     * @var string
     */
    protected $installation_id;

    /**
     * @param 	string 	$recipient_address
     * @param 	string 	$installation_id
     */
    public function __construct($recipient_address, $installation_id)
    {
        assert('is_string($recipient_address)');
        assert('is_string($installation_id)');
        $this->recipient_address = $recipient_address;
        $this->installation_id = $installation_id;
    }

    /**
     * @param 	\Throwable[] 	$errors 	List of error-messages
     * @return 	bool
     */
    public function send(array $errors)
    {
        $subject = 'Scheduled Events from ' . $this->installation_id;
        $body = $this->prepareMail($errors);
        $result = mail(
            $this->recipient_address,
            $subject,
            $body
        );
        return true;
    }

    /**
     * @param 	\Throwable[] 	$errors 	list of error-messages
     * @return 	string
     */
    private function prepareMail(array $errors)
    {
        $now = new \DateTime();
        $body = array(
            "Scheduled Events FAILED "
            . " at " . $this->installation_id
            . " on " . $now->format('Y-m-d H:i:s'),
            ''
        );

        foreach ($errors as $error) {
            $body[] = $error->getMessage();
            $body[] = $error->getTraceAsString();
            $body[] = '';
            $body[] = '----------------';
            $body[] = '';
        }

        return implode("\n", $body);
    }
}
