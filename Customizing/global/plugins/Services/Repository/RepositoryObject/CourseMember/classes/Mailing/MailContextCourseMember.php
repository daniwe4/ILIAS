<?php
namespace CaT\Plugins\CourseMember\Mailing;

require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextEnte.php');
require_once('./Services/TMS/Mailing/classes/class.ilTMSMailContextUser.php');

/**
 * This context expects a parallel user-context in TMSMail;
 * Edutracking-Object must be present in Course for this to have any effect.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class MailContextCourseMember extends \ilTMSMailContextEnte
{
    const TPL_PREFIX = "MEMBERLIST_";
    const TPL_PLACEHOLDER = "placeholder_tpl_id";

    protected static $PLACEHOLDERS = array(
        'IDD_USER_TIME' => 'placeholder_desc_idd_user_time',
        'MEMBER_STATUS' => 'placeholder_desc_member_status'
    );

    /**
     * @var \ilCourseMemberPlugin
     */
    protected $plugin;

    /**
     * @inheritdoc
     */
    public function placeholderIds()
    {
        $ids = array_keys(self::$PLACEHOLDERS);
        if (!\ilPluginAdmin::isPluginActive('xetr')) { //edutracking
            unset($ids[array_search('IDD_USER_TIME', $ids)]);
        }

        foreach ($this->getPlugin()->getAvailablePlaceholders() as $placeholder) {
            $ids[] = self::TPL_PREFIX . $placeholder;
        }
        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function placeholderDescriptionForId(string $placeholder_id) : string
    {
        $obj = \ilPluginAdmin::getPluginObjectById("xcmb");
        if (strpos($placeholder_id, self::TPL_PREFIX) === 0) {
            return $obj->txt(self::TPL_PLACEHOLDER);
        }
        $desc = self::$PLACEHOLDERS[$placeholder_id];
        return $obj->txt($desc);
    }

    /**
     * @inheritdoc
     */
    public function valueFor(string $placeholder_id, array $contexts = array()) : ?string
    {
        if (!in_array($placeholder_id, $this->placeholderIds())) {
            return null;
        }
        return $this->resolveValueFor($placeholder_id, $contexts);
    }

    /**
     * @param string $id
     * @param MailContexts[] $contexts
     * @return $string | null
     */
    protected function resolveValueFor($id, $contexts)
    {
        $actions = $this->owner->getActions();
        switch ($id) {
            case (strpos($id, self::TPL_PREFIX) === 0):
                $template_id = str_replace(self::TPL_PREFIX, "", $id);
                $link = $this->getLinkForSignatureListTemplate($template_id);
                return $link;

            case 'IDD_USER_TIME':
                $user_context = $this->getUserContext($contexts);
                if (!$user_context) {//there's nothing we can do w/o a user
                    return null;
                }
                $members = $actions->getMemberWithSavedLPSatus();

                foreach ($members as $key => $member) {
                    if ($user_context->getUsrId() === $member->getUserId()) {
                        $user_idd_time = $member->getIDDLearningTime();
                        return $this->transformMinutes($user_idd_time);
                    }
                }
                return null;

            case 'MEMBER_STATUS':
                $user_context = $this->getUserContext($contexts);
                if (!$user_context) {//there's nothing we can do w/o a user
                    return null;
                }
                $members = $actions->getMemberWithSavedLPSatus();

                foreach ($members as $key => $member) {
                    if ($user_context->getUsrId() === $member->getUserId()) {
                        return $member->getLPValue();
                    }
                }
                return null;

            default:
                return 'NOT RESOLVED: ' . $id;
        }
    }

    /**
     * find user context in contexts
     *
     * @param MailContexts[] $contexts
     * @return ilTMSMailContextUser | null
     */
    protected function getUserContext($contexts)
    {
        foreach ($contexts as $key => $context) {
            if (get_class($context) === 'ilTMSMailContextUser') {
                return $context;
            }
        }
        return null;
    }

    /**
     * Transforms the idd minutes into printable string
     *
     * @param int 	$minutes
     *
     * @return string
     */
    protected function transformMinutes($minutes)
    {
        if ($minutes === null) {
            return "";
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes - $hours * 60;
        return str_pad($hours, "2", "0", STR_PAD_LEFT) . ":" . str_pad($minutes, "2", "0", STR_PAD_LEFT);
    }

    protected function getPlugin()
    {
        if (is_null($this->plugin)) {
            $this->plugin = \ilPluginAdmin::getPluginObjectById('xcmb');
        }
        return $this->plugin;
    }

    protected function getLinkForSignatureListTemplate(string $template_mail_id) : string
    {
        if (!\ilPluginAdmin::isPluginActive('docdeliver')) {
            return "";
        }

        $template_id = $this->getPlugin()->getTemplateIdByMailPlaceholder($template_mail_id);
        if (is_null($template_id)) {
            return "";
        }

        /** @var ilDocumentDeliveryPlugin $xcmb */
        $docdeliver = \ilPluginAdmin::getPluginObjectById('docdeliver');
        return ILIAS_HTTP_PATH . '/' . $docdeliver->getLinkForSignatureList((int) $this->entity()->id(), $template_id);
    }
}
