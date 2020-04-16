<?php
namespace CaT\Plugins\Accomodation\Mailing;

use \CaT\Ente\Entity;
use ILIAS\TMS\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
/**
 * Placeholder-values, if mail is a reminder.
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */

class ilMailContextOACReminder extends \ilTMSMailContextEnte
{

    /**
     * @var ilLanguage
     */
    protected $g_lang;

    public function __construct($entity, $owner)
    {
        global $DIC;
        $this->g_lang = $DIC->language();
        $this->g_lang->loadLanguageModule("tms");
        parent::__construct($entity, $owner);
    }

    private static $PLACEHOLDER = array(
        'ACCOMODATION_REMINDER' => 'placeholder_desc_oac_reminder',
    );

    /**
     * @inheritdoc
     */
    public function valueFor($placeholder_id, $contexts = array())
    {
        switch ($placeholder_id) {
            case 'ACCOMODATION_REMINDER':
                return $this->g_lang->txt('placeholder_mailsubject_oac_reminder');
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
