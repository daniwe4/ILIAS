<?php
/**
 * @ilCtrl_Calls DomainConfigurationOverviewGUI: DomainConfigurationGUI
 */
use CaT\Plugins\OrguByMailDomain as OMD;

class DomainConfigurationOverviewGUI
{
    const CMD_OVERVIEW = 'overview';
    const CMD_REMOVE_DOMAIN = 'remove_domain';
    const CMD_REMOVE_DOMAINS = 'remove_domains';
    const CMD_REQUEST_REMOVE_DOMAIN = 'request_remove_domain';
    const CMD_REQUEST_REMOVE_DOMAINS = 'request_remove_domains';

    const GET_DOMAIN_ID = 'domain_id';
    const POST_DOMAIN_IDS = 'domain_ids';
    const POST_DOMAIN_ID = 'domain_id';

    public function __construct(
        \DomainConfigurationGUI $domain_configuration_gui,
        OMD\DomainConfigurationOverviewTableGUI $table,
        OMD\Configuration\Repository $config_repository,
        OMD\Orgus $orgus,
        \ilOrguByMailDomainPlugin $plugin,
        \ilCtrl $ctrl,
        \ilGlobalTemplateInterface $tpl
    ) {
        $this->domain_configuration_gui = $domain_configuration_gui;
        $this->table = $table;

        $this->config_repository = $config_repository;
        $this->orgus = $orgus;

        $this->plugin = $plugin;

        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
    }



    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $this->cmd = $this->ctrl->getCmd(self::CMD_OVERVIEW);
        switch ($next_class) {
            case 'domainconfigurationgui':
                $this->ctrl->forwardCommand($this->domain_configuration_gui);
                break;
            default:
                $this->table = $this->table
                                ->withParentCmd($this->cmd)
                                ->withParentObj($this);
                switch ($this->cmd) {
                    case self::CMD_OVERVIEW:
                        $this->overview();
                        break;
                    case self::CMD_REQUEST_REMOVE_DOMAINS:
                        $this->requestRemoveDomains();
                        break;
                    case self::CMD_REQUEST_REMOVE_DOMAIN:
                        $this->requestRemoveDomain();
                        break;
                    case self::CMD_REMOVE_DOMAINS:
                        $this->removeDomains();
                        break;
                    case self::CMD_REMOVE_DOMAIN:
                        $this->removeDomain();
                        break;
                    default:
                        $this->overview();
                }
        }
        return true;
    }

    protected function requestRemoveDomains()
    {
        $confirm = new ilConfirmationGUI();
        $domain_ids = $_POST['selected'];
        $domains = [];
        if (is_array($domain_ids)) {
            foreach ($domain_ids as $domain_id) {
                $domains[] = $this->config_repository->loadById((int) $domain_id)->getTitle();
                $confirm->addHiddenItem(self::POST_DOMAIN_IDS . '[]', $domain_id);
            }
            $confirm->setHeaderText(sprintf($this->plugin->txt('delete_domains_conf_head'), implode(', ', $domains)));
            $confirm->setFormAction($this->ctrl->getFormAction($this));
            $confirm->setConfirm($this->plugin->txt('delete'), self::CMD_REMOVE_DOMAINS);
            $confirm->setCancel($this->plugin->txt('cancel'), self::CMD_OVERVIEW);
            $this->tpl->setContent($confirm->getHTML());
        } else {
            \ilUtil::sendInfo($this->plugin->txt('no_domains_chosen'));
            $this->overview();
        }
    }

    protected function requestRemoveDomain()
    {
        $domain_id = (int) $_GET[self::GET_DOMAIN_ID];
        $domain = $this->config_repository->loadById($domain_id);
        $confirm = new ilConfirmationGUI();
        $confirm->addHiddenItem(self::POST_DOMAIN_ID, $domain_id);
        $confirm->setHeaderText(sprintf($this->plugin->txt('delete_domain_conf_head'), $domain->getTitle()));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->plugin->txt('delete'), self::CMD_REMOVE_DOMAIN);
        $confirm->setCancel($this->plugin->txt('cancel'), self::CMD_OVERVIEW);
        $this->tpl->setContent($confirm->getHTML());
    }

    protected function removeDomains()
    {
        $selected = $_POST[self::POST_DOMAIN_IDS];

        if (is_array($selected)) {
            foreach ($selected as $domain_id) {
                $this->config_repository->delete(
                    $this->config_repository->loadById((int) $domain_id)
                );
            }
        }
        \ilUtil::sendInfo($this->plugin->txt('domains_deleted'), true);
        $this->redirectOverview();
    }

    protected function removeDomain()
    {
        $domain_id = $_POST[self::POST_DOMAIN_ID];
        $this->config_repository->delete(
            $this->config_repository->loadById((int) $domain_id)
        );
        \ilUtil::sendInfo($this->plugin->txt('domain_deleted'), true);
        $this->redirectOverview();
    }

    public function overview()
    {
        $this->tpl->setContent(
            $this->creationLinkButton()->render() .
            $this->table->getHTML()
        );
    }

    protected function redirectOverview()
    {
        $this->ctrl->redirect($this, self::CMD_OVERVIEW);
    }

    protected function creationLinkButton()
    {
        $lb = ilLinkButton::getInstance();
        $lb->setUrl($this->ctrl->getLinkTargetByClass(
            DomainConfigurationGUI::class,
            DomainConfigurationGUI::CMD_CREATE_REQUEST
        ));
        $lb->setCaption($this->plugin->txt('create'), false);
        return $lb;
    }
}
