<?php
namespace CaT\Plugins\CourseMailing\Surroundings;

/**
 * The plugin needs information about its environment,
 * in this case about the configured mails available.
 */
class ilMailsAccessor
{
    protected $template_provider;

    protected $mailing_db;

    private function getProvider()
    {
        if (!$this->template_provider) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $this->template_provider = new \ilMailTemplateRepository($ilDB);
        }
        return $this->template_provider;
    }

    private function getMailingDB()
    {
        if (!$this->mailing_db) {
            global $DIC;
            $ilDB = $DIC['ilDB'];
            $this->mailing_db = new \ilTMSMailingDB($ilDB);
        }
        return $this->mailing_db;
    }

    /**
     * Get mail templates.
     *
     * @param string[] $contexts
     * @return ilMailTemplate[]
     */
    public function getTemplates($contexts)
    {
        $ret = array();
        $provider = $this->getProvider();

        if (count($contexts) > 0) {
            foreach ($contexts as $context) {
                $ret = array_merge($ret, $provider->findByContextId($context));
            }
        } else {
            foreach ($provider->getTableData() as $template_data) {
                $ret[] = $provider->findById((int) $template_data['tpl_id']);
            }
        }
        return $ret;
    }

    /**
     * Get a single mail template.
     *
     * @param int $id
     * @return \ilMailTemplate
     */
    public function getTemplate($id)
    {
        return $this->getProvider()->findById($id);
    }

    /**
     * Get a single mail template.
     *
     * @param string $ident
     * @return ilMailTemplate
     */
    public function getMailTemplateByIdent($ident)
    {
        list($id, $context) = $this->getMailingDB()->getTemplateIdByTitle($ident);
        return $this->getTemplate($id);
    }

    public function getMailTemplateDataByIdent($ident)
    {
        require_once('./Services/TMS/Mailing/classes/ilTMSMailing.php');
        $mailing = new \ilTMSMailing();
        return $mailing->getTemplateDataByIdent($ident);
    }
}
