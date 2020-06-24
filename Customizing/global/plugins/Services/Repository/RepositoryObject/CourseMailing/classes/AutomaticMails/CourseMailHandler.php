<?php
namespace CaT\Plugins\CourseMailing\AutomaticMails;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use \ILIAS\TMS\Mailing\MailingOccasion;
use \ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * Get mailing occasions for this course and event.
 * If mailing is not supressed, send mails.
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class CourseMailHandler
{
    use ilHandlerObjectHelper;

    public function __construct(int $crs_ref_id)
    {
        $this->crs_ref_id = $crs_ref_id;

        global $DIC;
        $this->logger = $DIC->logger()->root();
    }

    public function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    public function getEntityRefId()
    {
        return $this->crs_ref_id;
    }

    public function getMailingOccasions()
    {
        return $this->getComponentsOfType(MailingOccasion::class);
    }

    public function handleEvent($a_component, $a_event, $a_parameter)
    {
        $this->logger->write($a_event);
        $mails = array();
        $occasions = $this->getMailingOccasions(); //ente-components
        foreach ($occasions as $occasion) {
            //get coccasions that are interested in this particular event
            if (
                $occasion->doesProvideMailForEvent($a_event) &&
                $occasion->maybeSend()
            ) {
                //get the actual mail-configs;
                //there may be many, like user, tutor, superior....
                //a mail-config holds recipient, template-id and contexts
                $occasion_mails = $occasion->getMails($a_event, $a_parameter);
                $this->logger->write(get_class($occasion) . ' providing ' . count($occasion_mails) . ' mails');

                //scan mails for existing by recipient and template before merge
                foreach ($occasion_mails as $occasion_mail) {
                    $append = true;
                    foreach ($mails as $mail) {
                        if ($mail->getRecipient() == $occasion_mail->getRecipient() &&
                            $mail->getTemplateIdentifier() == $occasion_mail->getTemplateIdentifier()
                        ) {
                            $append = false;
                        }
                    }

                    if ($append) {
                        $mails[] = $occasion_mail;
                    } else {
                        $this->logger->write(
                            'Skip mail, because the recipient gets the same template already: '
                            . $occasion_mail->getTemplateIdentifier()
                        );
                    }
                }
            }
        }
        if ($this->preventMailing()) {
            $this->logger->write('All mails prevented by setting');
            return;
        }

        //now give the mails to the clerk.
        require_once("./Services/TMS/Mailing/classes/ilTMSMailing.php");
        $mailing = new \ilTMSMailing();
        $clerk = $mailing->getClerk();
        $clerk->process($mails, $a_event);
    }

    /**
     * Check all subtypes of a course whether it is CourseMail and
     * check the settings object for prevent_mailing.
     *
     * @return bool
     */
    protected function preventMailing()
    {
        $sub_items = $this->getAllChildrenOfByType($this->getEntityRefId(), 'xcml');
        foreach ($sub_items as $item) {
            if ($item->getSettings()->getPreventMailing()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all children by type recursive
     *
     * @param int 	$ref_id
     * @param string 	$search_type
     *
     * @return Object 	of search type
     */
    public function getAllChildrenOfByType($ref_id, $search_type)
    {
        global $DIC;
        $g_tree = $DIC->repositoryTree();
        $g_objDefinition = $DIC["objDefinition"];

        $childs = $g_tree->getChilds($ref_id);
        $ret = array();
        foreach ($childs as $child) {
            $type = $child["type"];
            if ($type == $search_type) {
                $ret[] = \ilObjectFactory::getInstanceByRefId($child["child"]);
            }

            if ($g_objDefinition->isContainer($type)) {
                $rec_ret = $this->getAllChildrenOfByType($child["child"], $search_type);
                if (!is_null($rec_ret)) {
                    $ret = array_merge($ret, $rec_ret);
                }
            }
        }

        return $ret;
    }
}
