<?php
namespace CaT\Plugins\CourseMailing\AutomaticMails;

use ILIAS\TMS\Mailing;

/**
 * placeholder-values, if invitation is a reminder
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilMailContextReminder implements Mailing\MailContext
{


    /**
     * @var ilLanguage
     */
    protected $g_lang;

    public function __construct()
    {
        global $DIC;
        $this->g_lang = $DIC->language();
        $this->g_lang->loadLanguageModule("tms");
    }

    private static $PLACEHOLDER = array(
        'REMINDER' => 'placeholder_desc_reminder',
    );

    /**
     * @inheritdoc
     */
    public function valueFor($placeholder_id, $contexts = array())
    {
        switch ($placeholder_id) {
            case 'REMINDER':
                return $this->g_lang->txt('tms_mailsubject_reminder');
            default:
                return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        return array_keys(self::$PLACEHOLDER);
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId($placeholder_id)
    {
        return $this->g_lang->txt(self::$PLACEHOLDER[$placeholder_id]);
    }
}
