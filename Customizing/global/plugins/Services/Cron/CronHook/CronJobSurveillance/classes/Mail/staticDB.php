<?php
namespace CaT\Plugins\CronJobSurveillance\Mail;

/**
 * static mail settings
 */
class staticDB implements DB
{
    /**
     * @inheritdoc
     */
    public function select()
    {
        return array(
            new MailSetting('ilias@cat06.de')
        );
    }

    /**
     * @inheritdoc
     */
    public function create($id, $mail_address)
    {
        throw new \Exception("This is a static DB", 1);
    }
    /**
     * @inheritdoc
     */
    public function update(MailSetting $setting)
    {
        throw new \Exception("This is a static DB", 1);
    }

    /**
     * @inheritdoc
     */
    public function deleteFor($id)
    {
        throw new \Exception("This is a static DB", 1);
    }
}
